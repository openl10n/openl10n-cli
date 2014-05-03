<?php

namespace Openl10n\Cli\File;

class FileInfo
{
	protected $pattern;

	protected $attributes;

	public function __construct($pattern, array $attributes)
	{
		$this->pattern = $pattern;
		$this->attributes = $attributes;
	}

	public function getPathname(array $excludeAttributes = array())
	{
		$attributes = $this->attributes;

		foreach ($excludeAttributes as $attr) {
			unset($attributes[$attr]);
		}

		$placeholders = array_map(function($attribute) {
			return "<$attribute>";
		}, array_keys($attributes));

		return str_replace($placeholders, $attributes, $this->pattern);
	}

	public function getAttribute($name)
	{
		if (!isset($this->attributes[$name])) {
			throw new \InvalidArgumentException(sprintf('Attribute %s does not exist', $name));
		}

		return $this->attributes[$name];
	}
}
