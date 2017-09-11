<?php

namespace Phpactor\Tests\Integration\Console;

use Phpactor\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Phpactor\Tests\Integration\SystemTestCase;

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
        $output = new BufferedOutput();
        $application = new Application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);
        $application->run(new ArrayInput([
            'command' => 'class:reflect',
            'name' => 'asd',
            '--format' => 'json',
        ]), $output);

    }

    public function testSerializesExceptions()
    {
        $output = new BufferedOutput();

        $application = new Application();
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'class:reflect',
            'name' => 'asd',
            '--format' => 'json',
        ]), $output);

        $out = json_decode($output->fetch(), true);
        $this->assertArrayHasKey('error', $out);
    }

    public function testCwd()
    {
        $this->loadProject('Animals');
        $output = new BufferedOutput();

        $application = new Application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);
        $exitCode = $application->run(new ArrayInput([
            'command' => 'config:dump',
            '--cwd' => $this->workspaceDir(),
        ]), $output);

        $this->assertEquals(0, $exitCode);
        $this->assertContains($this->workspaceDir(), $output->fetch());
    }
}
