<?php

namespace Openl10n\Cli\DependencyInjection\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilesExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container
            ->register('openl10n.resources_handler', 'Openl10n\Cli\Resource\ResourcesHandler')
            ->addArgument($config)
            ->addArgument(new Reference('openl10n.application'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition(ArrayNodeDefinition $node)
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
