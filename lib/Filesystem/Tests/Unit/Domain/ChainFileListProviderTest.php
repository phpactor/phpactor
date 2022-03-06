<?php

namespace Phpactor\Filesystem\Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use Phpactor\Filesystem\Domain\FileListProvider;
use Phpactor\Filesystem\Domain\ChainFileListProvider;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\FileList;
use Prophecy\PhpUnit\ProphecyTrait;

class ChainFileListProviderTest extends TestCase
{
    use ProphecyTrait;

    public function testChainFileListProvider(): void
    {
        $provider1 = $this->prophesize(FileListProvider::class);
        $provider2 = $this->prophesize(FileListProvider::class);

        $provider1->fileList()->willReturn(FileList::fromFilePaths([
            FilePath::fromString('/foobar1'),
            FilePath::fromString('/foobar2'),
        ]));
        $provider2->fileList()->willReturn(FileList::fromFilePaths([
            FilePath::fromString('/foobar3'),
        ]));

        $chain = new ChainFileListProvider([
            $provider1->reveal(),
            $provider2->reveal(),
        ]);

        $fileList = $chain->fileList();
        $this->assertInstanceOf(FileList::class, $fileList);
        $list = iterator_to_array($fileList);
        $this->assertCount(3, $list);
    }
}
