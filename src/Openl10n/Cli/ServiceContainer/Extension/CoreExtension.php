<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CoreExtension implements Extension
{
    /**
     * {@inheritdoc}
     */
    public function initialize(ContainerBuilder $container)
    {
        $container
            ->register('configuration.dumper', 'Openl10n\Cli\ServiceContainer\Configuration\ConfigurationDumper')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'core';
    }
}
