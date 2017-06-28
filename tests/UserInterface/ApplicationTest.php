<?php

namespace Phpactor\Tests\UserInterface;

use Phpactor\Tests\UserInterface\SystemTestCase;
use Phpactor\UserInterface\Console\Application;

class ApplicationTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
    }

    /**
     * @testdox It should throw an exception when an invalid configuration key is present.
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unknown configuration
     */
    public function testConfig()
    {
        file_put_contents(
            $this->workspaceDir() . '/.phpactor.yml',
            <<<'EOT'
foobar_invalid: something
EOT
        );

        chdir($this->workspaceDir());
        $application = new Application();
        $application->initialize();
    }
}
