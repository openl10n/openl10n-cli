<?php

namespace Openl10n\Cli\ServiceContainer\Configuration;

use Openl10n\Cli\ServiceContainer\Exception\ConfigurationLoadingException;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader
{
	protected $rootDirectory;
	protected $filename;

	/**
	 * @param string $filename Configuration file name
	 */
	public function __construct($rootDirectory, $filename)
	{
		$this->rootDirectory = $rootDirectory;
		$this->filename = $filename;
	}

	public function loadConfiguration()
	{
		$filepath = $this->rootDirectory.DIRECTORY_SEPARATOR.$this->filename;

		if (!file_exists($filepath)) {
            throw new ConfigurationLoadingException(
            	sprintf('Unable to find a configuration file in %s', $this->rootDirectory
            ));
        }

        return Yaml::parse(file_get_contents($filepath));
	}

	public function getRootDirectory()
	{
		return $this->rootDirectory;
	}

	public function setRootDirectory($rootDirectory)
	{
		$this->rootDirectory = $rootDirectory;
	}
}
