<?php

namespace Openl10n\Cli\File;

class Pattern
{
	protected $pattern;

	public function __construct($pattern)
	{
		$this->pattern = $pattern;
	}

	public function toString(array $attributes = array())
	{
		$placeholders = array_map(function ($attribute) {
            return "<$attribute>";
        }, array_keys($attributes));

        return str_replace($placeholders, $attributes, $this->pattern);
	}

	public function __toString()
	{
		return $this->toString();
	}
}
