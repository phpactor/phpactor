<?php

namespace Phpactor\Tests\UserInterface;

use Phpactor\Tests\UserInterface\SystemTestCase;
use Phpactor\UserInterface\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

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

    public function testSerializesExceptions()
    {
        $output = new BufferedOutput();

        $application = new Application();
        $application->initialize();
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'class:reflect',
            'name' => 'asd',
            '--format' => 'json',
        ]), $output);

        $out = json_decode($output->fetch(), true);
        $this->assertArrayHasKey('error', $out);
    }
}
