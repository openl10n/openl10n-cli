<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ProjectExtension implements ConfiguredExtension
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
            ->register('project_handler', 'Openl10n\Cli\Project\ProjectHandler')
            ->addArgument($config['slug'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $node)
    {
        $node
            ->beforeNormalization()
            ->ifString()
                ->then(function ($v) { return array(
                    'slug' => $v
                ); })
            ->end()
            ->children()
                ->scalarNode('slug')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
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
