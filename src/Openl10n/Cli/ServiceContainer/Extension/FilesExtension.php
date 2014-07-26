<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilesExtension implements ConfiguredExtension
{
    /**
     * {@inheritdoc}
     */
    public function initialize(ContainerBuilder $container)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container
            ->register('file_handler', 'Openl10n\Cli\File\FileHandler')
            ->addArgument(new Reference('configuration.loader'))
            ->addArgument($config)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $node)
    {
        $node
            ->prototype('array')
                ->beforeNormalization()
                    ->ifString()
                    ->then(function ($v) { return array('source' => $v); })
                ->end()
                ->children()
                    ->scalarNode('pattern')
                        ->isRequired()
                    ->end()
                    // ->scalarNode('translations')
                    //     ->defaultNull()
                    // ->end()
                    ->arrayNode('ignore')
                        ->beforeNormalization()
                            ->ifNull()
                            ->thenEmptyArray()
                            ->ifString()
                            ->then(function($v) { return array($v); })
                        ->end()
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('options')
                        ->beforeNormalization()
                            ->ifNull()
                            ->thenEmptyArray()
                        ->end()
                        ->prototype('variable')->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'files';
    }
}
