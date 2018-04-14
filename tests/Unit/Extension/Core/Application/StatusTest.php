<?php

namespace Phpactor\Tests\Unit\Extension\Core\Application;

use PHPUnit\Framework\TestCase;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\Core\Application\Status;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;

class StatusTest extends TestCase
{
    /**
     * @var FilesystemRegistry
     */
    private $registry;

    public function setUp()
    {
        $this->registry = $this->prophesize(FilesystemRegistry::class);
        $this->status = new Status($this->registry->reveal());
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
            SourceCodeFilesystemExtension::FILESYSTEM_SIMPLE,
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER,
        ]);
        $diagnostics = $this->status->check();
        $this->assertCount(2, $diagnostics['good']);
    }
}
