<?php

namespace Phpactor\ClassMover\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class AdapterTestCase extends TestCase
{
    protected function initWorkspace(): void
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists($this->workspacePath())) {
            $filesystem->remove($this->workspacePath());
        }

        $filesystem->mkdir($this->workspacePath());
    }

    protected function workspacePath(): string
    {
        return __DIR__ . '/../Assets/workspace';
    }

    protected function loadProject(): void
    {
        $projectPath = __DIR__ . '/../Assets/project';
        $filesystem = new Filesystem();
        $filesystem->mirror($projectPath, $this->workspacePath());
        chdir($this->workspacePath());
        exec('composer dumpautoload 2> /dev/null');
    }

    protected function getProjectAutoloader(): mixed
    {
        return require(__DIR__ . '/project/vendor/autoload.php');
    }
}
