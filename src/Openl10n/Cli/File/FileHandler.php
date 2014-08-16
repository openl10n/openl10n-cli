<?php

namespace Openl10n\Cli\File;

use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;

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

    public function addFileSet(FileSet $fileSet)
    {
        $this->fileSets[] = $fileSet;
    }

    public function getFileSets()
    {
        return $this->fileSets;
    }
}
