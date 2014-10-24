<?php

namespace Openl10n\Cli\File\Guess;

class SymfonyGuess implements PatternGuess
{
    /**
     * {@inheritdoc}
     */
    public function suggestPatterns($inDir)
    {
        if (!is_file($inDir.'/app/AppKernel.php')) {
            return [];
        }

        return $this->getPossiblePatterns();
    }

    protected function getPossiblePatterns()
    {
        return [
            'src/*/Bundle/*Bundle/Resources/translations/*.<locale>.*',
            'src/*/*Bundle/Resources/translations/*.<locale>.*',
            'app/Resources/translations/*.<locale>.*',
        ];
    }
}
