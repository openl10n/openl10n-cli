<?php

namespace Openl10n\Cli\File\Guess;

interface PatternGuess
{
    /**
     * Find available patterns matching project structure in given directory.
     *
     * @param string $inDir The root directory
     *
     * @return array Possible patterns
     */
    public function suggestPatterns($inDir);
}
