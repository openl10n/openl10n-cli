<?php

namespace Openl10n\Cli\File;

/**
 * Wrap a user defined pattern and give access to files
 */
class FileSet
{
    /**
     * @var string
     */
    protected $rootDirectory;

    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var array
     */
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

    /**
     * Retrieve files match by user pattern
     *
     * @return FileInfo[]
     */
    public function getFiles()
    {
        return $this->matcher->match($this->rootDirectory);
    }

    /**
     * Retrieve options associated to this pattern
     *
     * @param null $key
     *
     * @return array
     */
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
