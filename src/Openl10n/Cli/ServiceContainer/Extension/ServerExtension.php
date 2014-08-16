<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ServerExtension implements ConfiguredExtension
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
            ->register('api.config', 'Openl10n\Sdk\Config')
            ->addArgument($config['hostname'])
            ->addArgument($config['use_ssl'])
            ->addArgument($config['port'])
            ->addMethodCall('setAuth', array($config['username'], $config['password']))
        ;

        $container
            ->register('api', 'Openl10n\Sdk\Api')
            ->addArgument(new Reference('api.config'))
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
                ->then(function ($v) {
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
                        ->then(function ($v) {
                            return (boolean) $v;
                        })
                    ->end()
                    ->defaultFalse()
                ->end()
                ->integerNode('port')
                    ->defaultNull()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'server';
    }
}
