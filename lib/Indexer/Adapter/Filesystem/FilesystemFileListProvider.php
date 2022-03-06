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
     * @var array<string>
     */
    private $excludePatterns;

    /**
     * @var array<string>
     */
    private $includePatterns;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param array<string> $excludePatterns
     * @param array<string> $includePatterns
     */
    public function __construct(
        Filesystem $filesystem,
        array $includePatterns = [],
        array $excludePatterns = []
    ) {
        $this->filesystem = $filesystem;
        $this->includePatterns = $includePatterns;
        $this->excludePatterns = $excludePatterns;
    }

    public function provideFileList(Index $index, ?string $subPath = null): FileList
    {
        if (null !== $subPath && $this->filesystem->exists($subPath) && is_file($subPath)) {
            return FileList::fromSingleFilePath($subPath);
        }

        $files = $this->filesystem->fileList()->phpFiles();

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

        return FileList::fromInfoIterator($files->getIterator());
    }
}
