<?php

namespace Openl10n\Cli\Command;

use Openl10n\Cli\Config\Definition;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractCommand extends Command
{
    protected function getConfig()
    {
        $filepath = getcwd().'/openl10n.yml';

        $data = Yaml::parse(file_get_contents($filepath));

        $processor = new Processor();
        $config = $processor->processConfiguration(
            new Definition(),
            array('openl10n' => $data)
        );

        return $config;
    }
}
