<?php

namespace Phpactor\Tests;

use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Console\Input\ArrayInput;
use Phpactor\Container\Container;
use Phpactor\Phpactor;

abstract class IntegrationTestCase extends TestCase
{
    protected function workspaceDir()
    {
        return __DIR__ . '/Assets/Workspace';
    }

    protected function workspace(): Workspace
    {
        return Workspace::create($this->workspaceDir());
    }

    protected function assertSuccess(Process $process): void
    {
        if (true === $process->isSuccessful()) {
            $this->addToAssertionCount(1);
            return;
        }

        $this->fail(sprintf(
            'Process exited with code %d: %s %s',
            $process->getExitCode(),
            $process->getErrorOutput(),
            $process->getOutput()
        ));
    }

    protected function assertFailure(Process $process, $message): void
    {
        if (true === $process->isSuccessful()) {
            $this->fail('Process was a success');
        }

        if (null !== $message) {
            $this->assertStringContainsString($message, $process->getErrorOutput());
        }

        $this->addToAssertionCount(1);
    }

    protected function loadProject($name): void
    {
        $filesystem = new Filesystem();

        if (file_exists($this->cacheDir($name))) {
            $filesystem->mirror($this->cacheDir($name), $this->workspaceDir());
            return;
        }

        $filesystem->mirror(__DIR__ . '/Assets/Projects/' . $name, $this->workspaceDir());
        $currentDir = getcwd();
        chdir($this->workspaceDir());
        exec('git init');
        exec('git add *');
        exec('git commit -m "Test"');
        exec('composer install --quiet');
        chdir($currentDir);
        $this->cacheWorkspace($name);
    }

    protected function container(): Container
    {
        return Phpactor::boot(new ArrayInput([]), new BufferedOutput(), __DIR__ . '/../vendor');
    }

    private function cacheDir(string $name)
    {
        return __DIR__ . '/Assets/Cache/'.$name;
    }

    private function cacheWorkspace($name): void
    {
        $filesystem = new Filesystem();
        $cacheDir = $this->cacheDir($name);
        if (file_exists($cacheDir)) {
            $filesystem->remove($cacheDir);
        }
        mkdir($cacheDir, 0777, true);
        $filesystem->mirror($this->workspaceDir(), $this->cacheDir($name));
    }
}
