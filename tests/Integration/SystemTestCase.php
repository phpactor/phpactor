<?php

namespace Phpactor\Tests\Integration;

use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;

abstract class SystemTestCase extends TestCase
{
    protected function workspaceDir()
    {
        return dirname(__DIR__) . '/Assets/Workspace';
    }

    private function cacheDir(string $name)
    {
        return dirname(__DIR__) . '/Assets/Cache/'.$name;
    }

    private function cacheWorkspace($name)
    {
        $filesystem = new Filesystem();
        $cacheDir = $this->cacheDir($name);
        if (file_exists($cacheDir)) {
            $filesystem->remove($cacheDir);
        }
        mkdir($cacheDir, 0777, true);
        $filesystem->mirror($this->workspaceDir(), $this->cacheDir($name));
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
            $this->addToAssertionCount(1);
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

        $this->addToAssertionCount(1);
    }

    protected function loadProject($name)
    {
        $filesystem = new Filesystem();

        if (file_exists($this->cacheDir($name))) {
            $filesystem->mirror($this->cacheDir($name), $this->workspaceDir());
            return;
        }

        $filesystem->mirror(__DIR__ . '/../Assets/Projects/' . $name, $this->workspaceDir());
        chdir($this->workspaceDir());
        exec('git init');
        exec('git add *');
        exec('git commit -m "Test"');
        exec('composer install --quiet');
        $this->cacheWorkspace($name);
    }

    protected function phpactor(string $args, string $stdin = null): Process
    {
        chdir($this->workspaceDir());
        $bin = __DIR__ . '/../../bin/phpactor --verbose ';
        $process = new Process(sprintf(
            '%s %s'
        , $bin, $args), $this->workspaceDir(), [
            'PHPACTORTEST' => 1,
        ]);

        if ($stdin) {
            $process->setInput($stdin);
        }

        $process->run();

        return $process;
    }
}
