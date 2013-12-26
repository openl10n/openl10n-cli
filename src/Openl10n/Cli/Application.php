<?php

namespace Openl10n\Cli;

use Openl10n\Cli\Command as Command;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), array(
            new Command\InitCommand(),
            new Command\PullCommand(),
            new Command\PushCommand(),
        ));
    }
}
