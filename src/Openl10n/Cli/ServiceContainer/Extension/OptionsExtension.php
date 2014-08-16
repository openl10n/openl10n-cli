<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OptionsExtension implements ConfiguredExtension
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
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $node)
    {
        $node
            ->beforeNormalization()
                ->ifNull()
                ->thenEmptyArray()
            ->end()
            ->prototype('scalar')->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'options';
    }
}
