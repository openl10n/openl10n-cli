<?php

namespace Openl10n\Cli\ServiceContainer\Extension;

use Symfony\Component\DependencyInjection\ContainerBuilder;

interface Extension
{
    public function initialize(ContainerBuilder $container);

    public function getName();
}
