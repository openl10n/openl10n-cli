<?php

namespace Openl10n\Cli;

use Openl10n\Cli\Command as Command;
use Openl10n\Cli\DependencyInjection\Configuration;
use Openl10n\Cli\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication
{
    const DEFAULT_FILENAME = 'openl10n.yml';

    protected $container;

    public function getContainer()
    {
        if (null === $this->container) {
            $this->buildContainer();
        }

        return $this->container;
    }

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
        $filepath = $this->lookupFile();

        if (false === $filepath) {
            throw new \RuntimeException('Unable to find a configuration file');
        }

        return Yaml::parse(file_get_contents($filepath));
    }

    /**
     * Lookup for current configuration file.
     *
     * @param string $filename Filename to search
     *
     * @return string The full filepath of the configuration
     */
    protected function lookupFile($filename = self::DEFAULT_FILENAME)
    {
        $filepath = getcwd().'/'.$filename;

        if (!file_exists($filepath)) {
            return false;
        }

        return $filepath;
    }
}
