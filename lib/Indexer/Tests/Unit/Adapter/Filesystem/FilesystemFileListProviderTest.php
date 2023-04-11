<?php

namespace Phpactor\Indexer\Tests\Unit\Adapter\Filesystem;

use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Indexer\Adapter\Filesystem\FilesystemFileListProvider;
use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class FilesystemFileListProviderTest extends IntegrationTestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    private FilesystemFileListProvider $provider;

    private SimpleFilesystem $filesystem;

    /**
     * @var ObjectProphecy<Index>
     */
    private ObjectProphecy $index;

    protected function setUp(): void
    {
        $this->filesystem = new SimpleFilesystem(FilePath::fromString($this->workspace()->path()));
        $this->provider = new FilesystemFileListProvider($this->filesystem);
        $this->workspace()->reset();
        $this->index = $this->prophesize(Index::class);
    }

    public function testProvidesSingleFile(): void
    {
        $this->workspace()->put('foo.php', '<?php echo "hello";');
        $index = new InMemoryIndex();
        $list = $this->provider->provideFileList($index, $this->workspace()->path('foo.php'));
        self::assertEquals(1, $list->count());
    }

    public function testProvidesFromFilesystemRoot(): void
    {
        $this->workspace()->put('foo.php', '<?php echo "hello";');
        $this->workspace()->put('bar.php', '<?php echo "hello";');
        $index = new InMemoryIndex();

        $list = $this->provider->provideFileList($index);

        self::assertEquals(2, $list->count());
    }

    public function testDoesNotUseCacheIfSubPathProvided(): void
    {
        $this->workspace()->put('foo.php', '<?php echo "hello";');

        $this->index->isFresh()->shouldNotBeCalled();

        $list = $this->provider->provideFileList($this->index->reveal(), $this->workspace()->path());

        self::assertEquals(1, $list->count());
    }
}
