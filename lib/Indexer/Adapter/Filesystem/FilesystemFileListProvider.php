<?php

namespace Phpactor\Indexer\Adapter\Filesystem;

use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Indexer\Model\FileList;
use Phpactor\Indexer\Model\FileListProvider;
use SplFileInfo;
use Phpactor\Indexer\Model\Index;

class FilesystemFileListProvider implements FileListProvider
{
    /**
     * @param list<string> $excludePatterns
     * @param list<string> $includePatterns
     * @param list<string> $supportedExtensions
     */
    public function __construct(
        private Filesystem $filesystem,
        private array $includePatterns = [],
        private array $excludePatterns = [],
        private array $supportedExtensions = ['php'],
    ) {
    }

    public function provideFileList(Index $index, ?string $subPath = null): FileList
    {
        if (null !== $subPath && $this->filesystem->exists($subPath) && is_file($subPath)) {
            return FileList::fromSingleFilePath($subPath);
        }

        $files = $this->filesystem->fileList()->byExtensions($this->supportedExtensions);

        if ($this->includePatterns) {
            $files = $files->includePatterns($this->includePatterns);
        }

        if ($this->excludePatterns) {
            $files = $files->excludePatterns($this->excludePatterns);
        }

        if ($subPath) {
            $files = $files->within(FilePath::fromString($subPath));
        }

        if (!$subPath) {
            $files = $files->filter(function (SplFileInfo $fileInfo) use ($index) {
                return false === $index->isFresh($fileInfo);
            });
        }

        return FileList::fromInfoIterator($files->getSplFileInfoIterator());
    }
}
