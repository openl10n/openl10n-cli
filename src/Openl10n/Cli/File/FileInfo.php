<?php

namespace Openl10n\Cli\File;

class FileInfo
{
    /**
     * @var string
     */
    protected $rootDirectory;

    /**
     * @var Pattern
     */
    protected $pattern;

    /**
     * @var array
     */
    protected $attributes;

    public function __construct($rootDirectory, Pattern $pattern, array $attributes)
    {
        $this->rootDirectory = $rootDirectory;
        $this->pattern = $pattern;
        $this->attributes = $attributes;
    }

    public function getAbsolutePathname()
    {
        return realpath($this->rootDirectory.DIRECTORY_SEPARATOR.$this->getPathname());
    }

    public function getRelativePathname()
    {
        return $this->getPathname();
    }

    public function getPathname(array $usingAttributes = array())
    {
        $attributes = array_merge($this->attributes, $usingAttributes);

        return $this->pattern->toString($attributes);
    }

    public function getAttribute($name)
    {
        if (!isset($this->attributes[$name])) {
            throw new \InvalidArgumentException(sprintf('Attribute %s does not exist', $name));
        }

        return $this->attributes[$name];
    }
}
