<?php

namespace Openl10n\Cli\ServiceContainer;

use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationTree;
use Openl10n\Cli\ServiceContainer\Exception\ConfigurationProcessingException;
use Openl10n\Cli\ServiceContainer\Extension\ConfiguredExtension;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExtensionManager
{
    /**
     * @var array
     */
    protected $extensions;

    public function __construct(array $extensions = [])
    {
        $this->extensions = $extensions;
    }

    public function getExtensions()
    {
        return $this->extensions;
    }

    public function initialize(ContainerInterface $container)
    {
        foreach ($this->extensions as $extension) {
            $extension->initialize($container);
        }
    }

    public function load(array $rawConfigs, ContainerInterface $container)
    {
        // Validate configuration (only ConfiguredExtension are impacted)
        $extensions = array_filter($this->extensions, function ($extension) {
            return $extension instanceof ConfiguredExtension;
        });

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
}
