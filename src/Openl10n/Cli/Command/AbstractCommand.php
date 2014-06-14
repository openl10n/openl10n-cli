<?php

namespace Openl10n\Cli\Command;

use Openl10n\Cli\Config\Definition;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command
{
    const DEFAULT_FILENAME = 'openl10n.yml';

    protected function get($serviceName)
    {
        return $this->getApplication()->getContainer()->get($serviceName);
    }
}
