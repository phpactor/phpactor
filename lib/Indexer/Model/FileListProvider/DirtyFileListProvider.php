<?php

namespace Phpactor\Indexer\Model\FileListProvider;

use Generator;
use Phpactor\Indexer\Model\DirtyDocumentTracker;
use Phpactor\Indexer\Model\FileList;
use Phpactor\Indexer\Model\FileListProvider;
use Phpactor\Indexer\Model\Index;
use Phpactor\TextDocument\TextDocumentUri;
use RuntimeException;
use SplFileInfo;

class DirtyFileListProvider implements FileListProvider, DirtyDocumentTracker
{
    /**
     * @var array<string, bool>
     */
    private array $seen = [];

    public function __construct(private readonly string $dirtyPath)
    {
    }

    public function markDirty(TextDocumentUri $uri): void
    {
        if (isset($this->seen[$uri->path()])) {
            return;
        }

        $handle = @fopen($this->dirtyPath, 'a');
        if (false === $handle) {
            throw new RuntimeException(sprintf(
                'Dirty index file path "%s" cannot be created, maybe the directory does not exist?',
                $this->dirtyPath
            ));
        }
        fwrite($handle, $uri->path() . "\n");
        fclose($handle);
        $this->seen[$uri->path()] = true;
    }

    public function provideFileList(Index $index, ?string $subPath = null): FileList
    {
        return FileList::fromInfoIterator($this->paths());
    }

    /**
     * @return Generator<SplFileInfo>
     */
    private function paths(): Generator
    {
        $contents = @file_get_contents($this->dirtyPath);
        if (false === $contents) {
            return;
        }

        $paths = explode("\n", $contents);
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }
            yield new SplFileInfo($path);
        }

        unlink($this->dirtyPath);
    }
}
