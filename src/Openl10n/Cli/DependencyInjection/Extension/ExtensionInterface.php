<?php

namespace Openl10n\Cli\DependencyInjection\Extension;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface ExtensionInterface
{
	/**
	 * Register services into the container.
	 *
	 * @param array            $config    The (validated) configuration of the extension
	 * @param ContainerBuilder $container The container
	 */
	public function load(array $config, ContainerBuilder $container);

	/**
	 * Set configuration tree definition
	 *
	 * @param ArrayNodeDefinition $node The configuration node
	 */
	public function setDefinition(ArrayNodeDefinition $node);

	/**
	 * The name of the extension.
	 *
	 * This correspond to the value of the root definition node in the configuration.
	 *
	 * @return string The name
	 */
	public function getName();
}
