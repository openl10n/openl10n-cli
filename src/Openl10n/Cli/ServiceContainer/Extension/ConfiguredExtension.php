<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ConfiguredExtension extends Extension
{
	public function configure(ArrayNodeDefinition $node);

	public function load(array $config, ContainerBuilder $container);
}
