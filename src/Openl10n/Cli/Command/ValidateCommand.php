<?php

namespace Openl10n\Cli\Command;

use Openl10n\Cli\DependencyInjection\Configuration;
use Openl10n\Cli\DependencyInjection\Extension;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class ValidateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('validate')
            ->setDefinition(array(
                new InputArgument('file', InputArgument::OPTIONAL, 'path to the config file', './openl10n.yml')
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        if (!file_exists($file)) {
            $output->writeln("<error>$file not found.</error>");
            return 1;
        } elseif (!is_readable($file)) {
            $output->writeln("<error>$file is not readable.</error>");
            return 1;
        }

        $parser = new Parser();
        try {
            $config = $parser->parse(file_get_contents($file));
        } catch(ParseException $e) {
            $output->writeln(sprintf('<error>%s.</error>', $e->getMessage()));
            return 1;
        }

        $configuration = new Configuration(array(
            'server' => new Extension\ServerExtension(),
            'project' => new Extension\ProjectExtension(),
            'files' => new Extension\FilesExtension(),
            'options' => new Extension\OptionsExtension(),
        ));

        $processor = new Processor();
        try {
            $processor->processConfiguration($configuration, ['openl10n' => $config]);
        } catch (InvalidConfigurationException $e) {
            $output->writeln(sprintf('<error>%s.</error>', $e->getMessage()));
            return 1;
        }

        $output->writeln("<info>$file is valid.</info>");

        return 0;
    }
}
