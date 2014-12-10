<?php

namespace Openl10n\Cli\Command;

use KevinGH\Amend\Command as AmendCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends AmendCommand
{
    public function __construct()
    {
        parent::__construct('self-update');

        $this->setManifestUri('https://cdn.openl10n.io/cli/shared/manifest.json');
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();
    }
}
