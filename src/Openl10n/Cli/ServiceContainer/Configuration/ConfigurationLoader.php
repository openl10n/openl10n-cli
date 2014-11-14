<?php

namespace Openl10n\Cli\ServiceContainer\Configuration;

use Openl10n\Cli\ServiceContainer\Exception\ConfigurationLoadingException;
use Symfony\Component\Yaml\Yaml;

class ConfigurationLoader
{
    /**
     * @var string
     */
    protected $rootDirectory;

    /**
     * @var string
     */
    protected $configFilepath;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @param string $rootDirectory Directory containing the config file
     * @param string $filename      Configuration file name
     */
    public function __construct($rootDirectory, $filename)
    {
        $this->rootDirectory = $rootDirectory;
        $this->filename = $filename;
    }

    /**
     * Read the config file and return the configuration array.
     *
     * @return array The configuration
     */
    public function loadConfiguration()
    {
        $filepath = $this->getConfigurationFilepath();

        if (!file_exists($filepath)) {
            throw new ConfigurationLoadingException(
                sprintf('Unable to find a configuration file in %s', $this->rootDirectory
            ));
        }

        return Yaml::parse(file_get_contents($filepath));
    }

    /**
     * @return string The configuration filepath
     */
    public function getConfigurationFilepath()
    {
        return $this->configFilepath ?: $this->rootDirectory.DIRECTORY_SEPARATOR.$this->filename;
    }

    /**
     * @param $configFilepath string The configuration filepath
     */
    public function setConfigurationFilepath($configFilepath)
    {
        $this->configFilepath = $configFilepath;
    }

    /**
     * @return string The root directory
     */
    public function getRootDirectory()
    {
        return $this->rootDirectory;
    }

    /**
     * @param string $rootDirectory The root directory
     */
    public function setRootDirectory($rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }
}
