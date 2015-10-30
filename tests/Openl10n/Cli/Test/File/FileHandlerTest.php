<?php

namespace Openl10n\Cli\Test\File;

use Openl10n\Cli\File\FileHandler;
use Openl10n\Cli\File\FileSet;
use Openl10n\Cli\File\Matcher;

class FileHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFileSets()
    {
        $configurationLoader = $this->prophesize('Openl10n\Cli\ServiceContainer\Configuration\ConfigurationLoader');
        $filesConfiguration = array(
            array(
                'pattern' => 'foo/bar',
                'options' => array(
                    'foo' => 'bar'
                )
            )
        );
        $configurationLoader->getRootDirectory()
            ->willReturn('/foo/');

        $handler = new FileHandler($configurationLoader->reveal(), $filesConfiguration);

        $expectedFilesets = array(
            new FileSet('/foo/', new Matcher('foo/bar'), array('foo' => 'bar'))
        );

        $this->assertEquals($expectedFilesets, $handler->getFileSets());
    }
}
