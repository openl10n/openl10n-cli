<?php

namespace Openl10n\Cli\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Definition implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('openl10n');

        $this
            ->addServerSection($rootNode)
            ->addProjectSection($rootNode)
            ->addFileSection($rootNode)
            ->addOptionsSection($rootNode)
        ;

        return $treeBuilder;
    }

    private function addServerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('server')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($v) { return array(
                            'hostname' => $v
                        ); })
                    ->end()
                    ->children()
                        ->scalarNode('hostname')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('username')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('password')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $this;
    }

    private function addProjectSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('project')
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($v) { return array(
                            'host' => $v
                        ); })
                    ->end()
                    ->children()
                        ->scalarNode('slug')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('locales')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $this;
    }

    private function addFileSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('files')
                    ->prototype('array')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function($v) { return array('source' => $v); })
                        ->end()
                        ->children()
                            ->scalarNode('source')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $this;
    }

    private function addOptionsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('options')
                ->end()
            ->end();

        return $this;
    }
}
