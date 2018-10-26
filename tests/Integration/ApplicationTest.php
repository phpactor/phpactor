<?php

namespace Phpactor\Tests\Integration;

use Phpactor\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Phpactor\Tests\IntegrationTestCase;
use Phpactor\MapResolver\InvalidConfig;

class ApplicationTest extends IntegrationTestCase
{
    public function setUp()
    {
        $this->workspace()->reset();
    }

    public function tearDown()
    {
        $this->workspace()->reset();
    }

    public function application(): Application
    {
        return new Application(__DIR__ . '/../../vendor');
    }

    public function testConfig()
    {
        $this->expectException(InvalidConfig::class);
        $this->expectExceptionMessage('Keys "foobar_invalid" are not known');
        file_put_contents(
            $this->workspaceDir() . '/.phpactor.yml',
            <<<'EOT'
foobar_invalid: something
EOT
        );

        chdir($this->workspaceDir());
        $output = new BufferedOutput();
        $application = $this->application();
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

        $application = $this->application();
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

        $application = $this->application();
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);
        $exitCode = $application->run(new ArrayInput([
            'command' => 'config:dump',
            '--working-dir' => $this->workspaceDir(),
        ]), $output);

        $this->assertEquals(0, $exitCode);
        $this->assertContains($this->workspaceDir(), $output->fetch());
    }
}
