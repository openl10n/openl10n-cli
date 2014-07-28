<?php

namespace Openl10n\Cli\File\Guess;

class SilexKitchenEditionGuess implements PatternGuess
{
    /**
     * {@inheritdoc}
     */
    public function suggestPatterns($inDir)
    {
        if (!is_dir($inDir.'/resources/locales')) {
            return [];
        }

        return ['resources/locales/<locale>.yml'];
    }
}
