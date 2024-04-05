<?php

namespace Phpactor\Filesystem\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

abstract class IntegrationTestCase extends TestCase
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
        return realpath(__DIR__.'/..') . '/Workspace';
    }

    protected function loadProject(): void
    {
        $projectPath = __DIR__.'/project';
        $filesystem = new Filesystem();
        $filesystem->mirror($projectPath, $this->workspacePath());
        chdir($this->workspacePath());
        exec('composer dumpautoload --quiet');
    }

    protected function getProjectAutoloader(): string
    {
        return require __DIR__.'/project/vendor/autoload.php';
    }
}
