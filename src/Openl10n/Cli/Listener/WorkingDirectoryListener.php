<?php

namespace Openl10n\Cli\Listener;

use Openl10n\Cli\Command\AbstractCommand;
use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkingDirectoryListener implements EventSubscriberInterface
{
    protected $configurKationLoader;

    public function __construct(ConfigurationLoader $configurationLoader)
    {
        $this->configurationLoader = $configurationLoader;
    }

    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::COMMAND => array('onConsoleCommand', 10),
        );
    }

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $input = $event->getInput();
        $inputDefinition = $event->getCommand()->getApplication()->getDefinition();

        $inputDefinition->addOption(
            new InputOption('working-dir', null, InputOption::VALUE_REQUIRED, 'The root directory of the project', null)
        );

        $inputDefinition->addOption(
            new InputOption('config-file', null, InputOption::VALUE_REQUIRED, 'The filepath of the configuration file', null)
        );

        $event->getCommand()->mergeApplicationDefinition();
        $input->bind($event->getCommand()->getDefinition());

        // Ignore commands which are not from the project (like the "help command")
        if (!$event->getCommand() instanceof AbstractCommand) {
            return;
        }

        if (null !== $workingDir = $input->getOption('working-dir')) {
            $this->configurationLoader->setRootDirectory($workingDir);
        }

        if (null !== $configPath = $input->getOption('config-file')) {
            $this->configurationLoader->setConfigurationFilepath($configPath);
        }
    }
}
