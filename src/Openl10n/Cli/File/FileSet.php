<?php

namespace Openl10n\Cli\File;

class FileSet
{
	protected $rootDirectory;
	protected $pattern;
	protected $options;

	/**
	 * @param string $rootDirectory
	 * @param string $pattern
	 * @param array  $options
	 */
	public function __construct($rootDirectory, $pattern, array $options = array())
	{
		$this->rootDirectory = $rootDirectory;
		$this->pattern = $pattern;
		$this->options = $options;
	}

	public function getFiles()
	{
		return (new Matcher())->match($this->pattern, $this->rootDirectory);
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
