<?php

namespace Openl10n\Cli\File;

use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;

/**
 * Handle the set of FileSet (patterns) defined by user in his config file
 */
class FileHandler
{
    /**
     * @var ConfigurationLoader
     */
    protected $configurationLoader;

    /**
     * @var FileSet[]
     */
    protected $fileSets;

    /**
     * @param ConfigurationLoader $configurationLoader
     * @param array $filesConfiguration
     */
    public function __construct(ConfigurationLoader $configurationLoader, array $filesConfiguration = array())
    {
        $this->configurationLoader = $configurationLoader;
        $this->fileSets = [];

        foreach ($filesConfiguration as $config) {
            $rootDir = $this->configurationLoader->getRootDirectory();
            $matcher = new Matcher($config['pattern']);
            $options = $config['options'];
            $fileSet = new FileSet($rootDir, $matcher, $options);

            $this->addFileSet($fileSet);
        }
    }

    /**
     * Add a FileSet to the list handle by this FileHandler
     *
     * @param FileSet $fileSet
     */
    public function addFileSet(FileSet $fileSet)
    {
        $this->fileSets[] = $fileSet;
    }

    /**
     * Retrieve FileSet list
     *
     * @return array|FileSet[]
     */
    public function getFileSets()
    {
        return $this->fileSets;
    }
}
