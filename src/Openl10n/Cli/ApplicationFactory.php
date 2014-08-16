<?php

namespace Openl10n\Cli;

use Openl10n\Cli\Command;
use Openl10n\Cli\Listener\WorkingDirectoryListener;
use Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader;
use Openl10n\Cli\ServiceContainer\Extension;
use Openl10n\Cli\ServiceContainer\ExtensionManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ApplicationFactory
{
    const NAME = 'openl10n';

    const VERSION = '@package_version@';

    const CONFIG_FILENAME = '.openl10n.yml';

    /**
     * Create the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $configurationLoader = $this->createConfigurationLoader();
        $extensionManager = $this->createExtensionManager();
        $eventDispatcher = $this->createEventDispatcher();

        $eventDispatcher->addSubscriber(new WorkingDirectoryListener($configurationLoader));

        $application = new Application(self::NAME, self::VERSION, $configurationLoader, $extensionManager);
        $application->addCommands($this->getDefaultCommands());
        $application->setDispatcher($eventDispatcher);

        return $application;
    }

    /**
     * @return array The default commands
     */
    protected function getDefaultCommands()
    {
        return [
            new Command\InitCommand(),
            new Command\PullCommand(),
            new Command\PushCommand(),
        ];
    }

    /**
     * @return array The default extensions
     */
    protected function getDefaultExtensions()
    {
        return [
            new Extension\CoreExtension(),
            new Extension\ServerExtension(),
            new Extension\ProjectExtension(),
            new Extension\FilesExtension(),
            new Extension\OptionsExtension(),
        ];
    }

    /**
     * @return ConfigurationLoader The configuration loader
     */
    protected function createConfigurationLoader()
    {
        return new ConfigurationLoader(getcwd(), self::CONFIG_FILENAME);
    }

    /**
     * @return ExtensionManager The extension manager
     */
    public function createExtensionManager()
    {
        return new ExtensionManager($this->getDefaultExtensions());
    }

    /**
     * @return EventDispatcher An EventDispatcher
     */
    private function createEventDispatcher()
    {
        return new EventDispatcher();
    }
}
