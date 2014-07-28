<?php

namespace Openl10n\Cli\ServiceContainer\Configuration;

use Openl10n\Cli\ServiceContainer\Exception\ConfigurationLoadingException;
use Openl10n\Cli\ServiceContainer\ExtensionManager;
use Symfony\Component\Yaml\Yaml;

class ConfigurationDumper
{
    /**
     * Dump configuration array to valid file content.
     *
     * @param array $config The configuration
     *
     * @return string The dumped configuration
     */
    public function dumpConfiguration(array $config)
    {
        $content = '';
        foreach ($this->configuration as $name => $section) {
            $content .= Yaml::dump([$name => $section], 4).PHP_EOL;
        }

        return $content;
    }
}
