<?php

namespace Openl10n\Cli\Test\File;

use Openl10n\Cli\File\FileSet;

class FileSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFiles()
    {
        $matcher = $this->prophesize('Openl10n\Cli\File\Matcher');

        $matcher->match('/foo/')
            ->willReturn(array('foo', 'bar'));

        $fileSet = new FileSet('/foo/', $matcher->reveal());

        $this->assertSame(
            array('foo', 'bar'),
            $fileSet->getFiles()
        );
    }

    public function testGetAllOptions()
    {
        $matcher = $this->prophesize('Openl10n\Cli\File\Matcher');
        $fileSet = new FileSet('/foo/', $matcher->reveal(), array('options'));

        $this->assertSame(
            array('options'),
            $fileSet->getOptions()
        );
    }

    public function testGetEmptyOptions()
    {
        $matcher = $this->prophesize('Openl10n\Cli\File\Matcher');
        $fileSet = new FileSet('/foo/', $matcher->reveal());

        $this->assertSame(
            array(),
            $fileSet->getOptions()
        );
    }

    public function testGetSpecificOption()
    {
        $matcher = $this->prophesize('Openl10n\Cli\File\Matcher');
        $fileSet = new FileSet('/foo/', $matcher->reveal(), array('option' => array('foo')));

        $this->assertSame(
            array('foo'),
            $fileSet->getOptions('option')
        );
    }
}
