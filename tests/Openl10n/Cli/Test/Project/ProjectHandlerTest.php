<?php

namespace Openl10n\Cli\Test\Project;

use Openl10n\Cli\Project\ProjectHandler;

class ProjectHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProjectSlug()
    {
        $handler = new ProjectHandler('foo');

        $this->assertSame('foo', $handler->getProjectSlug());
    }
}
