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
                        ->then(function($v) {
                            $home = getenv('HOME');
                            $filepath = $home.'/.openl10n/server.conf';
                            $data = array();
                            if (file_exists($filepath)) {
                                $data = parse_ini_file($filepath, true);
                            }
                            if (isset($data[$v])) {
                                return $data[$v];
                            }

                            return array(
                                'hostname' => $v
                            );
                        })
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
                        ->booleanNode('use_ssl')
                            // Because data parsed from an INI file is not
                            // interpreted as boolean, then cast automatically.
                            ->beforeNormalization()
                            ->ifString()
                                ->then(function($v) { return (boolean) $v; })
                            ->end()
                            ->defaultFalse()
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
                            ->scalarNode('source')
                                ->isRequired()
                            ->end()
                            ->scalarNode('dump')
                                ->defaultNull()
                            ->end()
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
                    ->end()
                ->end()
            ->end();

        return $this;
    }

    private function addOptionsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('options')
                    ->beforeNormalization()
                        ->ifNull()
                        ->thenEmptyArray()
                    ->end()
                    ->prototype('scalar')->end()
                ->end()
            ->end();

        return $this;
    }
}
