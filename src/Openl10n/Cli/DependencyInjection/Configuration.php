<?php

namespace Openl10n\Cli\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    protected $extensions;

    public function __construct(array $extensions = array())
    {
        $this->extensions = $extensions;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('openl10n');

        foreach ($this->extensions as $extension) {
            $rootName = $extension->getName();

            $node = $rootNode->children()->arrayNode($rootName);

            $extension->setDefinition($node);

            //$node->end()->end();
        }

        return $treeBuilder;
    }
}
