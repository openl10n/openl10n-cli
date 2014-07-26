<?php

namespace Openl10n\Cli;

use Openl10n\Cli\Command as Command;
use Openl10n\Cli\DependencyInjection\Configuration;
use Openl10n\Cli\DependencyInjection\Extension;
use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;
use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationTree;
use Openl10n\Cli\ServiceContainer\Exception\ConfigurationLoadingException;
use Openl10n\Cli\ServiceContainer\Exception\ConfigurationProcessingException;
use Openl10n\Cli\ServiceContainer\ExtensionManager;
use Openl10n\Cli\ServiceContainer\Extension\ConfiguredExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApplication
{
    protected $container;
    protected $ignoreMissingConfiguration;

    public function __construct($name, $version, ConfigurationLoader $configurationLoader, ExtensionManager $extensionManager)
    {
        parent::__construct($name, $version);

        $this->configurationLoader = $configurationLoader;
        $this->extensionManager = $extensionManager;

        $this->ignoreMissingConfiguration = false;
    }

    public function getContainer()
    {
        if (null === $this->container) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    /**
     * Force container to be recreated.
     */
    public function destroyContainer()
    {
        $this->container = null;
    }

    /**
     * Ignore when the configuration file is missing.
     *
     * Useful for commands which don't need the configurated services
     * such as the `init` command.
     *
     * @param boolean $value
     */
    public function ignoreMissingConfiguration($value = true)
    {
        $this->ignoreMissingConfiguration = $value;
    }

    protected function createContainer()
    {
        $container = new ContainerBuilder();

        // Default configuration.
        $container->set('application', $this);
        $container->set('configuration.loader', $this->configurationLoader);

        // Initialize extensions.
        $this->initializeExtensions($container);

        // Load configuration.
        try {
            $this->loadConfiguration($container);
        } catch (ConfigurationLoadingException $e) {
            // No configuration file found.
            // Continue with restricted services if `ignoreMissingConfiguration`
            // has been specified.
            if (!$this->ignoreMissingConfiguration) {
                throw $e;
            }
        }

        // Process compiler pass & compile container
        $container->compile();

        return $container;
    }

    protected function initializeExtensions(ContainerInterface $container)
    {
        foreach ($this->extensionManager->getExtensions() as $extension) {
            $extension->initialize($container);
        }
    }

    protected function loadConfiguration(ContainerInterface $container)
    {
        // Validate configuration (only ConfiguredExtension are impacted)
        $extensions = array_filter($this->extensionManager->getExtensions(), function($extension) {
            return $extension instanceof ConfiguredExtension;
        });

        // Read the configuration
        $rawConfigs = $this->configurationLoader->loadConfiguration();

        // Process configuration
        $configurationTree = new ConfigurationTree($extensions);
        $configs = (new Processor())->processConfiguration(
            $configurationTree,
            array('openl10n' => $rawConfigs)
        );

        // Load extension with correct configuration
        foreach ($extensions as $extension) {
            $name = $extension->getName();

            if (!isset($configs[$name])) {
                throw new ConfigurationProcessingException(sprintf('Missing configuration for node "%s"', $name));
            }

            $config = $configs[$name];

            $extension->load($config, $container);
        }
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
}
