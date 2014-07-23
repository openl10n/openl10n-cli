<?php

namespace Openl10n\Cli;

use Openl10n\Cli\Command as Command;
use Openl10n\Cli\DependencyInjection\Configuration;
use Openl10n\Cli\DependencyInjection\Extension;
use Openl10n\Cli\Listener;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    const CONFIG_FILENAME = '.openl10n.yml';

    protected $container;
    protected $workingDir;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, $version)
    {
        parent::__construct($name, $version);

        $dispatcher = $this->createEventDispatcher();

        $this->setDispatcher($dispatcher);
        $this->workingDir = getcwd();
    }

    public function getContainer()
    {
        if (null === $this->container) {
            $this->buildContainer();
        }

        return $this->container;
    }

    public function getWorkingDirectory()
    {
        return $this->workingDir;
    }

    public function setWorkingDirectory($workingDir)
    {
        if (!is_dir($workingDir)) {
            throw new \RuntimeException(sprintf('%s is not a valid directory', $workingDir));
        }

        $this->workingDir = $workingDir;
    }

    /**
     * Lookup for current configuration file.
     *
     * @param string $filename Filename to search
     *
     * @return string The full filepath of the configuration
     */
    public function getConfigPathname()
    {
        return $this->workingDir.'/'.self::CONFIG_FILENAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), array(
            new Command\InitCommand(),
            new Command\PullCommand(),
            new Command\PushCommand(),
        ));
    }

    /**
     * Build container.
     */
    protected function buildContainer()
    {
        $this->container = new ContainerBuilder();

        $rawConfig = $this->getRawConfig();

        $extensions = [
            'server' => new Extension\ServerExtension(),
            'project' => new Extension\ProjectExtension(),
            'files' => new Extension\FilesExtension(),
            'options' => new Extension\OptionsExtension(),
        ];

        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Configuration($extensions),
            array('openl10n' => $rawConfig)
        );

        // Register current application instance
        $this->container->set('openl10n.application', $this);

        // Load each extension
        foreach ($config as $name => $parameters) {
            $extensions[$name]->load($parameters, $this->container);
        }
    }

    /**
     * Get configuration parameters.
     *
     * @return array Parameters tree
     */
    protected function getRawConfig()
    {
        $filepath = $this->getConfigPathname();

        if (!file_exists($filepath)) {
            throw new \RuntimeException('Unable to find a configuration file');
        }

        return Yaml::parse(file_get_contents($filepath));
    }

    /**
     * Create an event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    private function createEventDispatcher()
    {
        $dispatcher = new EventDispatcher();

        // Add event subscribers to dispatcher
        $listeners = [
            new Listener\WorkingDirectoryListener()
        ];

        foreach ($listeners as $listener) {
            $listener->setApplication($this);
            $dispatcher->addSubscriber($listener);
        }

        return $dispatcher;
    }
}
