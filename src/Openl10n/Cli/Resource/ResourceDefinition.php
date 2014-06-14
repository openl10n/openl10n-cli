<?php

namespace Openl10n\Cli\Resource;

class ResourceDefinition
{
	protected $pattern;
	protected $files;
	protected $options;

	public function __construct($pattern, array $files, array $options = array())
	{
		$this->pattern = $pattern;
		$this->files = $files;
		$this->options = $options;
	}

	public function getPathnameForLocale($locale)
	{
		if (isset($this->files[$locale])) {
			return $this->files[$locale];
		}

		return str_replace('<locale>', $locale, $this->pattern);
	}

	public function getFiles()
	{
		return $this->files;
	}

	public function getOptions()
	{
		return $this->options;
	}
}
