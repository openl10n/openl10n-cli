<?php

namespace Openl10n\Cli\DependencyInjection\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OptionsExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        //var_dump($config);
    }

    /**
     * {@inheritdoc}
     */
	public function setDefinition(ArrayNodeDefinition $node)
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
