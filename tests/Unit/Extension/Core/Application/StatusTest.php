<?php

namespace Phpactor\Tests\Unit\Extension\Core\Application;

use PHPUnit\Framework\TestCase;
use Phpactor\Config\Paths;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilesystemExtraExtension;

class StatusTest extends TestCase
{
    /**
     * @var FilesystemRegistry
     */
    private $registry;

    public function setUp()
    {
        $this->registry = $this->prophesize(FilesystemRegistry::class);
        $this->paths = new Paths();
        $this->status = new Status($this->registry->reveal(), $this->paths, '/path/to/here');
    }

    public function testStatusNoComposerOrGit()
    {
        $this->registry->names()->willReturn(['simple']);
        $diagnostics = $this->status->check();
        $this->assertCount(3, $diagnostics['bad']);
    }

    public function testStatusComposerOrGit()
    {
        $this->registry->names()->willReturn([
            SourceCodeFilesystemExtraExtension::FILESYSTEM_SIMPLE,
            SourceCodeFilesystemExtraExtension::FILESYSTEM_GIT,
            SourceCodeFilesystemExtraExtension::FILESYSTEM_COMPOSER,
        ]);
        $diagnostics = $this->status->check();
        $this->assertCount(2, $diagnostics['good']);
    }
}
