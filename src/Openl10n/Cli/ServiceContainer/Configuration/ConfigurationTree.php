<?php

namespace Openl10n\Cli\ServiceContainer\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTree implements ConfigurationInterface
{
    /**
     * @var array
     */
    protected $extensions;

    public function __construct(array $extensions)
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

            $extension->configure($node);
        }

        return $treeBuilder;
    }
}
