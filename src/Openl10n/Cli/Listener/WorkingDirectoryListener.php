<?php

namespace Openl10n\Cli\Listener;

use Openl10n\Cli\Application;
use Openl10n\Cli\Command\AbstractCommand;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkingDirectoryListener implements EventSubscriberInterface
{
    protected $application;

    public static function getSubscribedEvents()
    {
        return array(
            ConsoleEvents::COMMAND => array('onConsoleCommand', 10),
        );
    }

    public function setApplication(Application $application)
    {
        $this->application = $application;
    }

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $input = $event->getInput();
        $inputDefinition = $event->getCommand()->getApplication()->getDefinition();

        $inputDefinition->addOption(
            new InputOption('working-dir', null, InputOption::VALUE_REQUIRED, 'The directory of the configuration file', null)
        );

        $event->getCommand()->mergeApplicationDefinition();
        $input->bind($event->getCommand()->getDefinition());

        // Ignore commands which are not from the project (like the "help command")
        if (!$event->getCommand() instanceof AbstractCommand) {
            return;
        }

        if (null !== $workingDir = $input->getOption('working-dir')) {
            $this->application->setWorkingDirectory($workingDir);
        }
    }
}
