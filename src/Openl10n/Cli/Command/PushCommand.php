<?php

namespace Openl10n\Cli\Command;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Openl10n\Sdk\Model\Project;
use Openl10n\Sdk\Api;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;
use Symfony\Component\Yaml\Yaml;

class PushCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('push')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $data = $this->getConfig();

        $api = new Api(array(
            'hostname' => $data['server']['hostname'],
            'username' => $data['server']['username'],
            'password' => $data['server']['password'],
            'scheme' => $data['server']['use_ssl'] ? 'https' : 'http',
        ));

        $projectSlug = $data['project']['slug'];

        // Get project
        try {
            $project = $api->getProject($projectSlug);
        } catch (ClientErrorResponseException $e) {
            if (404 !== $e->getResponse()->getStatusCode()) {
                throw $e;
            }

            $output->writeln(sprintf(
                '<info>Creating project <comment>%s</comment></info>',
                $data['project']['slug']
            ));

            $project = new Project($projectSlug);
            $project->setName(ucfirst($projectSlug));

            $command = $api->createProject($project);
        }

        // Ensure locales are present
        $languages = $api->getLanguages($projectSlug);
        $locales = array();
        foreach ($languages as $language) {
            $locales[] = $language['locale'];
        }

        $localesToCreate = array_diff($data['project']['locales'], $locales);

        foreach ($localesToCreate as $locale) {
            $output->writeln(sprintf(
                '<info>Adding locale <comment>%s</comment></info>',
                $locale
            ));

            $command = $api->createLanguage($projectSlug, $locale);
        }

        // Import files
        foreach ($data['files'] as $file) {
            $pattern = $file['source'];
            $pattern = str_replace('<domain>', '___DOMAIN_PLACEHOLDER___', $pattern);
            $pattern = str_replace('<locale>', '___LOCALE_PLACEHOLDER___', $pattern);
            $pattern = Glob::toRegex($pattern);

            $pattern = str_replace('___DOMAIN_PLACEHOLDER___', '(?P<domain>\w+)', $pattern);
            $pattern = str_replace('___LOCALE_PLACEHOLDER___', '(?P<locale>\w+)', $pattern);

            $options = $file['options'];
            $importOptions = isset($options['push']) ? (array) $options['push'] : array();

            $finder = new Finder();
            $finder->in(getcwd())->path($pattern);
            foreach ($finder->files() as $file) {
                if (!preg_match($pattern, $file->getRelativePathname(), $matches)) {
                    $output->writeln(sprintf(
                        'File %s does match pattern %s',
                        $file->getRelativePathname(),
                        $pattern
                    ));
                    continue;
                }

                $output->writeln(sprintf(
                    '<info>Importing file <comment>%s</comment></info>',
                    $file->getRelativePathname()
                ));

                $api->importFile($projectSlug, $file, $matches['domain'], $matches['locale'], $importOptions);
            }
        }
    }
}
