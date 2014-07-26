<?php

namespace Openl10n\Cli\File;

use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;

class FileHandler
{
	protected $configurationLoader;
	protected $fileSets;

	public function __construct(ConfigurationLoader $configurationLoader, array $filesConfiguration = array())
	{
		$this->configurationLoader = $configurationLoader;
		$this->fileSets = [];

		foreach ($filesConfiguration as $config) {
			$rootDir = $this->configurationLoader->getRootDirectory();
			$pattern = $config['pattern'];
			$options = $config['options'];
			$fileSet = new FileSet($rootDir, $pattern, $options);

			$this->addFileSet($fileSet);
		}
	}

	public function addFileSet(FileSet $fileSet)
	{
		$this->fileSets[] = $fileSet;
	}

	public function getFileSets()
	{
		return $this->fileSets;
	}
}
