<?php

namespace Phpactor\Indexer\Tests\Unit\Model\FileListProvider;

use Phpactor\Indexer\Adapter\Php\InMemory\InMemoryIndex;
use Phpactor\Indexer\Model\FileListProvider\DirtyFileListProvider;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;

class DirtyFileListProviderTest extends IntegrationTestCase
{
    const EXAMPLE_FILE_1 = 'foobar';
    const EXAMPLE_FILE_2 = 'barfoo';


    public function testTrackAndProvideDirtyDocuments(): void
    {
        $tracker = $this->createProvider();
        $this->workspace()->put(self::EXAMPLE_FILE_1, '');
        $this->workspace()->put(self::EXAMPLE_FILE_2, '');
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_1)));
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_2)));

        $files = $tracker->provideFileList(new InMemoryIndex([]));

        self::assertCount(2, $files);
    }

    public function testReleasedDirtyFilesAreNoLongerTracked(): void
    {
        $tracker = $this->createProvider();
        $this->workspace()->put(self::EXAMPLE_FILE_1, '');
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_1)));

        self::assertFileExists($this->workspace()->path('dirty'));

        $files = $tracker->provideFileList(new InMemoryIndex([]));
        self::assertCount(1, $files);

        $files = $tracker->provideFileList(new InMemoryIndex([]));
        self::assertCount(0, $files);
        self::assertFileDoesNotExist($this->workspace()->path('dirty'));
    }

    public function testDoNotDuplicate(): void
    {
        $tracker = $this->createProvider();
        $this->workspace()->put(self::EXAMPLE_FILE_1, '');
        $this->workspace()->put(self::EXAMPLE_FILE_2, '');
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_1)));
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_2)));
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_2)));
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_2)));

        $files = $tracker->provideFileList(new InMemoryIndex([]));

        self::assertCount(2, $files);
    }

    public function testNonExistingFile(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dirty index');
        $tracker = $this->createProvider('foobar/no');
        $tracker->markDirty(TextDocumentUri::fromString($this->workspace()->path(self::EXAMPLE_FILE_1)));
    }

    private function createProvider(string $path = 'dirty'): DirtyFileListProvider
    {
        $tracker = new DirtyFileListProvider($this->workspace()->path($path));
        return $tracker;
    }
}
