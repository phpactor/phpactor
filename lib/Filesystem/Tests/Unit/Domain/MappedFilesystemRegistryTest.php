<?php

namespace Phpactor\Filesystem\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Filesystem\Domain\MappedFilesystemRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use InvalidArgumentException;

class MappedFilesystemRegistryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function setUp(): void
    {
        $this->filesystem = $this->prophesize(Filesystem::class);
    }

    public function testRetrievesFilesystems(): void
    {
        $registry = $this->createRegistry([
            'foobar' => $this->filesystem->reveal()
        ]);

        $filesystem = $registry->get('foobar');

        $this->assertEquals($this->filesystem->reveal(), $filesystem);
    }

    public function testHasFilesystem(): void
    {
        $registry = $this->createRegistry([
            'foobar' => $this->filesystem->reveal()
        ]);

        $this->assertTrue($registry->has('foobar'));
        $this->assertFalse($registry->has('barbar'));
    }

    public function testExceptionOnNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown filesystem "barfoo"');
        $registry = $this->createRegistry([
            'foobar' => $this->filesystem->reveal()
        ]);

        $registry->get('barfoo');
    }

    private function createRegistry(array $filesystems)
    {
        return new MappedFilesystemRegistry($filesystems);
    }
}
