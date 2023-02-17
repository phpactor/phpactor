<?php

namespace Phpactor\Tests\Unit\Extension\Core\Application;

use PHPUnit\Framework\TestCase;
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\Core\Application\Status;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class StatusTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy<FilesystemRegistry> */
    private ObjectProphecy $registry;

    /** @var ObjectProphecy<PhpVersionResolver> */
    private ObjectProphecy $resolver;

    private PathCandidates $paths;

    private Status $status;

    public function setUp(): void
    {
        $this->registry = $this->prophesize(FilesystemRegistry::class);
        $this->resolver = $this->prophesize(PhpVersionResolver::class);
        $this->paths = new PathCandidates([]);
        $this->status = new Status($this->registry->reveal(), $this->paths, '/path/to/here', $this->resolver->reveal());
    }

    public function testStatusNoComposerOrGit(): void
    {
        $this->registry->names()->willReturn(['simple']);
        $diagnostics = $this->status->check();

        // should be git and composer error +/- xdebug warning
        $this->assertGreaterThanOrEqual(2, $diagnostics['bad']);
    }

    public function testStatusComposerOrGit(): void
    {
        $this->registry->names()->willReturn([
            SourceCodeFilesystemExtension::FILESYSTEM_SIMPLE,
            SourceCodeFilesystemExtension::FILESYSTEM_GIT,
            SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER,
        ]);
        $diagnostics = $this->status->check();

        // should be git and composer error +/- xdebug warning
        $this->assertGreaterThanOrEqual(2, $diagnostics['good']);
    }
}
