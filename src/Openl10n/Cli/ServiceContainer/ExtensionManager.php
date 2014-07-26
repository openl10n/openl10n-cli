<?php

namespace Openl10n\Cli\ServiceContainer;

class ExtensionManager
{
	/**
	 * @var array
	 */
	protected $extensions;

	public function __construct(array $extensions = [])
	{
		$this->extensions = $extensions;
	}

	public function getExtensions()
	{
		return $this->extensions;
	}
}
