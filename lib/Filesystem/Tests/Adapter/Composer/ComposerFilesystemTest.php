<?php

namespace Phpactor\Filesystem\Tests\Adapter\Composer;

use Phpactor\Filesystem\Adapter\Composer\ComposerFilesystem;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Tests\Adapter\AdapterTestCase;
use Phpactor\Filesystem\Domain\Filesystem;

class ComposerFilesystemTest extends AdapterTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        chdir($this->workspacePath());
        exec('composer dumpautoload --quiet');
    }

    public function testClassmap(): void
    {
        $fileList = $this->filesystem()->fileList();
        $location = $this->filesystem()->createPath('src/Hello/Goodbye.php');
        $fileList = $fileList->named('DB.php');
        $this->assertCount(1, $fileList);

        foreach ($fileList as $file) {
            $this->assertInstanceOf(FilePath::class, $file);
        }
    }

    protected function filesystem(): Filesystem
    {
        static $classLoader;

        if (!$classLoader) {
            $classLoader = require 'vendor/autoload.php';
        }

        return new ComposerFilesystem(FilePath::fromString($this->workspacePath()), $classLoader);
    }
}
