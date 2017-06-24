<?php

namespace Phpactor\Tests\UserInterface;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;

class SystemTestCase extends \PHPUnit_Framework_TestCase
{
    protected function workspaceDir()
    {
        return __DIR__ . '/../Assets/Workspace';
    }

    protected function initWorkspace()
    {
        $filesystem = new Filesystem();
        if (file_exists($this->workspaceDir())) {
            $filesystem->remove($this->workspaceDir());
        }
        $filesystem->mkdir($this->workspaceDir());
    }

    protected function assertSuccess(Process $process)
    {
        if (true === $process->isSuccessful()) {
            return;
        }

        $this->fail(sprintf(
            'Process exited with code %d: %s %s', $process->getExitCode(), $process->getErrorOutput(), $process->getOutput()
        ));
    }

    protected function assertFailure(Process $process, $message)
    {
        if (true === $process->isSuccessful()) {
            $this->fail('Process was a success');
        }

        if (null !== $message) {
            $this->assertContains($message, $process->getErrorOutput());
        }
    }

    protected function loadProject($name)
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__ . '/../Assets/Projects/' . $name, $this->workspaceDir());
        chdir($this->workspaceDir());
        exec('git init');
        exec('git add *');
        exec('git commit -m "Test"');
        exec('composer install --quiet');
    }

    protected function phpactor(string $args)
    {
        chdir($this->workspaceDir());
        $bin = __DIR__ . '/../../bin/phpactor';
        $process = new Process(sprintf(
            '%s %s --verbose'
        , $bin, $args));
        $process->run();

        return $process;
    }
}
