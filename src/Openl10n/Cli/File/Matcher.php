<?php

namespace Openl10n\Cli\File;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;

class Matcher
{
    /**
     * @var string
     */
    protected $pattern;

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }

    public function match($inDir)
    {
        $results = [];

        // Pop out placeholders from given pattern
        preg_match_all('/<(?P<placeholder>\w+)>/', $this->pattern, $matches);
        $placeholders = $matches['placeholder'];

        // Replace placeholder by a valid string
        foreach ($placeholders as $placeholder) {
            $this->pattern = str_replace(
                "<${placeholder}>",
                "___${placeholder}_placeholder___",
                $this->pattern
            );
        }

        // Transform pattern to regex
        $regex = Glob::toRegex($this->pattern);
        $regex = trim($regex, '#');

        // Englobe every part of the regex into a matching pattern.
        // Then build final regex by concatening parts & placeholders.
        // Be sure to make copy of placeholders.
        $counter = 0;
        $parts = preg_split('/___\w+_placeholder___/', $regex);
        $placeholderStack = $placeholders;

        $regex = '';
        while ($part = array_shift($parts)) {
            $counter++;
            $regex .= "(?P<___part_${counter}___>${part})";

            if (null !== $placeholder = array_shift($placeholderStack)) {
                $regex .= "(?P<${placeholder}>\w+)";
            }
        }

        // Re-add regex delimiters
        $regex = '#'.$regex.'#';

        // Find files
        $finder = new Finder();
        $finder->in($inDir)->path($regex);
        foreach ($finder->files() as $file) {
            
            // Replacing Windows backslashes by linux slashes
            $relativePathnameSanitized = str_replace("\\", "/", $file->getRelativePathname());
            
            if (!preg_match($regex, $relativePathnameSanitized, $matches)) {
                // Should not happen, but it better to check
                continue;
            }

            $parts = [];
            $attributes = [];
            foreach ($matches as $key => $match) {
                if (!is_string($key)) {
                    continue;
                }

                if (preg_match('/___part_\d+___/', $key)) {
                    $parts[] = $match;
                } else {
                    $attributes[$key] = $match;
                }
            }


            $filePattern = '';
            $placeholderStack = $placeholders; // make copy of placeholders

            do {
                $part = array_shift($parts);
                $filePattern .= $part;

                if (null !== $placeholder = array_shift($placeholderStack)) {
                    $filePattern .= "<${placeholder}>";
                }
            } while(null !== $part);

            $results[] = new FileInfo(
                $inDir,
                new Pattern($filePattern),
                $attributes
            );
        }

        return $results;
    }
}
