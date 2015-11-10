<?php

namespace Openl10n\Cli\Command;

use GuzzleHttp\Exception\ClientException;
use Openl10n\Cli\ServiceContainer\Exception\ConfigurationLoadingException;
use Openl10n\Sdk\Model\Project;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class InitCommand extends AbstractCommand
{
    protected $configuration = array();

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Create the configuration file and initialize the project')
            ->setDefinition(array(
                new InputArgument('url', InputArgument::OPTIONAL, 'URL of the openl10n instance (eg. http://user:userpass@openl10n.dev)'),
                new InputArgument('project', InputArgument::OPTIONAL, 'Slug of the project'),
                new InputArgument('pattern', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'Pattern of the translation files'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        //
        // First try to read the configuration file if exists
        //
        $this->getApplication()->ignoreMissingConfiguration();
        $configurationLoader = $this->get('configuration.loader');

        try {
            // Init configuration by manually read configuration file (if already exists)
            $this->configuration = $configurationLoader->loadConfiguration();
        } catch (ConfigurationLoadingException $e) {
            $this->configuration = [
                'server'  => [],
                'project' => null,
                'files'   => [],
            ];
        }

        //
        // Then add config from input
        //
        $server = [
            'scheme' => null,
            'user' => null,
            'pass' => null,
            'port' => null,
            'host' => null
        ];

        if (null !== $url = $input->getArgument('url')) {
            $urlParts = parse_url($url);

            if (false === $urlParts) {
                throw new InvalidArgumentException("$url is not a valid URL");
            }

            $server = array_merge($server, $urlParts);
        }

        if (null !== $hostname = $server['host']) {
            $this->configuration['server']['hostname'] = $hostname;
        }

        if (null !== $scheme = $server['scheme']) {
            $this->configuration['server']['use_ssl'] = 'https' === $scheme;
        }

        if (isset($this->configuration['server']['use_ssl']) && false === $this->configuration['server']['use_ssl']) {
            unset($this->configuration['server']['use_ssl']);
        }

        if (null !== $port = $server['port']) {
            $this->configuration['server']['port'] = $port;
        }

        if (null !== $username = $server['user']) {
            $this->configuration['server']['username'] = $username;
        }

        if (null !== $password = $server['pass']) {
            $this->configuration['server']['password'] = $password;
        }

        if (null !== $projectSlug = $input->getArgument('project')) {
            $this->configuration['project'] = $projectSlug;
        }

        if (array() !== $patterns = $input->getArgument('pattern')) {
            $this->configuration['files'] = $patterns;
        }

        // If no file are already set, try to find possible ones
        if (empty($this->configuration['files'])) {
            $inDir = $this->get('configuration.loader')->getRootDirectory();
            $this->configuration['files'] = $this->get('file.pattern_guess')->suggestPatterns($inDir);
        }

        // If no pattern found, add example of file pattern
        if (empty($this->configuration['files'])) {
            $this->configuration['files'][] = 'path/to/translations.<locale>.yml';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        // Server
        if (!isset($this->configuration['server']['hostname'])) {
            $this->configuration['server']['hostname'] = $dialog->ask($output, '<info>Hostname</info> [<comment>openl10n.dev</comment>]: ', 'openl10n.dev');
        }

        if (!isset($this->configuration['server']['use_ssl'])) {
            $this->configuration['server']['use_ssl'] = $dialog->askConfirmation($output, '<info>Enable ssl</info> [<comment>no</comment>]? ', false);
        }

        if (false === $this->configuration['server']['use_ssl']) {
            unset($this->configuration['server']['use_ssl']);
        }

        if (!isset($this->configuration['server']['username'])) {
            $currentUser = get_current_user();
            $this->configuration['server']['username'] = $dialog->ask($output, "<info>Username</info> [<comment>$currentUser</comment>]: ", $currentUser);
        }

        if (!isset($this->configuration['server']['password'])) {
            $currentUser = get_current_user();
            $this->configuration['server']['password'] = $dialog->askHiddenResponseAndValidate(
                $output,
                '<info>Password</info> []: ',
                function ($answer) {
                    if ('' === trim($answer)) {
                        throw new \RuntimeException('The password can not be empty.');
                    }

                    return $answer;
                },
                false,
                false
            );
        }

        // Project
        if (!isset($this->configuration['project'])) {
            $project = strtolower(basename(realpath($this->get('configuration.loader')->getRootDirectory())));
            $this->configuration['project'] = $dialog->ask($output, "<info>Project's slug</info> [<comment>$project</comment>]: ", $project);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $configurationLoader = $this->get('configuration.loader');

        // Dump configuration
        $content = $this->get('configuration.dumper')->dumpConfiguration($this->configuration);

        if ($input->isInteractive()) {
            $output->writeln(['', $content]);
        }

        if (!$dialog->askConfirmation($output, '<info>Do you confirm generation</info> [<comment>yes</comment>]? ')) {
            return 1;
        }

        file_put_contents($configurationLoader->getConfigurationFilepath(), $content);

        // Destroy current container to force recreate it with configured service
        $this->getApplication()->destroyContainer();

        $projectApi = $this->get('api')->getEntryPoint('project');

        try {
            $projectSlug = $this->configuration['project'];
            $project = $projectApi->get($projectSlug);

            return;
        } catch (ClientException $e) {
            if ('404' !== $e->getResponse()->getStatusCode()) {
                throw $e;
            }
        }

        $output->writeln('');
        if ($dialog->askConfirmation($output, '<info>Would you like to create the project</info> [<comment>yes</comment>]? ')) {
            $project = new Project($projectSlug);

            $defaultName = ucfirst($project->getSlug());
            $name = $dialog->ask($output, "<info>Project's name</info> [<comment>$defaultName</comment>]: ", $defaultName);
            $project->setName($name);

            $defaultLocale = $dialog->ask($output, "<info>Default locale</info> [<comment>en</comment>]: ", 'en');
            $project->setDefaultLocale($defaultLocale);

            try {
                $projectApi->create($project);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                return 1;
            }
        }
    }
}
