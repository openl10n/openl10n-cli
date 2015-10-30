<?php

namespace Openl10n\Cli\File;

class FileSet
{
    protected $rootDirectory;
    protected $matcher;
    protected $options;

    /**
     * @param string  $rootDirectory
     * @param Matcher $matcher
     * @param array   $options
     */
    public function __construct($rootDirectory, Matcher $matcher, array $options = array())
    {
        $this->rootDirectory = $rootDirectory;
        $this->matcher = $matcher;
        $this->options = $options;
    }

    public function getFiles()
    {
        return $this->matcher->match($this->rootDirectory);
    }

    public function getOptions($key = null)
    {
        if (null === $key) {
            return $this->options;
        }

        if (!isset($this->options[$key])) {
            return array();
        }

        return $this->options[$key];
    }
}
