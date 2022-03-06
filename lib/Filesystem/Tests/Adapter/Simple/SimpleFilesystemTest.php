<?php

namespace Phpactor\Filesystem\Tests\Adapter\Simple;

use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Tests\Adapter\AdapterTestCase;
use Phpactor\Filesystem\Domain\Filesystem;

class SimpleFilesystemTest extends AdapterTestCase
{
    protected function filesystem(): Filesystem
    {
        return new SimpleFilesystem(FilePath::fromString($this->workspacePath()));
    }
}
