<?php

namespace Openl10n\Cli\File\Guess;

class ComposableGuess implements PatternGuess
{
    protected $guessList;

    public function __construct(array $guessList)
    {
        $this->guessList = $guessList;
    }

    /**
     * {@inheritdoc}
     */
    public function suggestPatterns($inDir)
    {
        $patterns = [];

        foreach ($this->guessList as $guess) {
            $patterns = array_merge($patterns, $guess->suggestPatterns($inDir));
        }

        return $patterns;
    }
}
