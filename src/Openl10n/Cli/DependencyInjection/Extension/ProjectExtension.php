<?php

namespace Openl10n\Cli\DependencyInjection\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ProjectExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container
            ->register('openl10n.project_handler', 'Openl10n\Cli\Project\ProjectHandler')
            ->addArgument(new Reference('openl10n.api'))
            ->addArgument($config['id'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefinition(ArrayNodeDefinition $node)
    {
        $node
            ->beforeNormalization()
            ->ifString()
                ->then(function ($v) { return array(
                    'id' => $v
                ); })
            ->end()
            ->children()
                ->scalarNode('id')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                // ->arrayNode('locales')
                //     ->prototype('scalar')->end()
                // ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'project';
    }
}
