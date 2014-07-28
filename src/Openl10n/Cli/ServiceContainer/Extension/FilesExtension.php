<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FilesExtension implements ConfiguredExtension
{
    /**
     * {@inheritdoc}
     */
    public function initialize(ContainerBuilder $container)
    {
        $container
            ->register('file.pattern_guess.symfony', 'Openl10n\Cli\File\Guess\SymfonyGuess')
        ;
        $container
            ->register('file.pattern_guess.silex_kitchen_edition', 'Openl10n\Cli\File\Guess\SilexKitchenEditionGuess')
        ;

        $container
            ->register('file.pattern_guess.composable', 'Openl10n\Cli\File\Guess\ComposableGuess')
            ->addArgument([
                new Reference('file.pattern_guess.symfony'),
                new Reference('file.pattern_guess.silex_kitchen_edition'),
            ])
        ;

        $container
            ->setAlias('file.pattern_guess', 'file.pattern_guess.composable')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container
            ->register('file_handler', 'Openl10n\Cli\File\FileHandler')
            ->addArgument(new Reference('configuration.loader'))
            ->addArgument($config)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(ArrayNodeDefinition $node)
    {
        $node
            ->beforeNormalization()
                // If config contains only a single string (without array)
                // then convert to an array with single string.
                ->ifString()
                ->then(function ($v) { return array($v); })
            ->end()
            ->prototype('array')
                ->beforeNormalization()
                    // Item could be abbreviated with only a single string
                    // which is interpreted as the pattern.
                    ->ifString()
                    ->then(function ($v) { return ['pattern' => $v]; })
                ->end()
                ->children()
                    ->scalarNode('pattern')
                        ->isRequired()
                    ->end()
                    ->arrayNode('ignore')
                        ->beforeNormalization()
                            ->ifNull()
                            ->thenEmptyArray()
                            ->ifString()
                            ->then(function ($v) { return array($v); })
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
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'files';
    }
}
