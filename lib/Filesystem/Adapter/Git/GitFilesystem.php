<?php

namespace Phpactor\Filesystem\Adapter\Git;

use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FileListProvider;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\CopyReport;
use Phpactor\Filesystem\Domain\Exception\NotSupported;
use SplFileInfo;
use Symfony\Component\Process\Process;
use ArrayIterator;
use InvalidArgumentException;

class GitFilesystem extends SimpleFilesystem
{
    private FilePath $path;

    public function __construct(FilePath $path, FileListProvider $fileListProvider = null)
    {
        parent::__construct($path, $fileListProvider);
        $this->path = $path;

        if (false === file_exists($path->__toString().'/.git')) {
            throw new NotSupported(
                'The cwd does not seem to be a git repository root (could not find .git folder)'
            );
        }
    }


    public function fileList(): FileList
    {
        $gitFiles = $this->exec([
            'ls-files',
            '--cached',
            '--others',
            '--exclude-standard'
        ]);
        $files = [];

        foreach (explode("\n", $gitFiles) as $gitFile) {
            $files[] = new SplFileInfo((string) $this->path->makeAbsoluteFromString($gitFile));
        }

        return FileList::fromIterator(new ArrayIterator($files));
    }

    public function remove(FilePath|string $path): void
    {
        $path = FilePath::fromUnknown($path);
        if (false === $this->trackedByGit($path)) {
            parent::remove($path);
            return;
        }

        if ($path->isDirectory()) {
            $this->exec(['rm', '-r', '-f', $path->path()]);
            return;
        }

        $this->exec(['rm', '-f', $path->path()]);
    }

    public function move(FilePath|string $srcPath, FilePath|string $destPath): void
    {
        $srcPath = FilePath::fromUnknown($srcPath);
        $destPath = FilePath::fromUnknown($destPath);

        if (false === $this->trackedByGit($srcPath)) {
            parent::move($srcPath, $destPath);
            return;
        }

        $this->exec([
            'mv',
            $srcPath->path(),
            $destPath->path()
        ]);
    }

    public function copy(FilePath|string $srcPath, FilePath|string $destPath): CopyReport
    {
        $srcPath = FilePath::fromUnknown($srcPath);
        $destPath = FilePath::fromUnknown($destPath);
        $list = parent::copy($srcPath, $destPath);
        $this->exec(['add', $destPath->__toString()]);

        return $list;
    }

    public function createPath(string $path): FilePath
    {
        return $this->path->makeAbsoluteFromString($path);
    }

    /**
     * @param array<string> $cmd
     */
    private function exec(array $cmd): string
    {
        $process = new Process(array_merge(['git'], $cmd), $this->path);
        $process->run();

        if ($process->getExitCode() !== 0) {
            throw new InvalidArgumentException(sprintf(
                'Could not execute git command "%s", exit code "%s", output "%s"',
                implode(' ', $cmd),
                $process->getExitCode(),
                $process->getOutput()
            ));
        }

        return $process->getOutput();
    }

    private function trackedByGit(FilePath $file): bool
    {
        $out = $this->exec(['ls-files', (string) $file]);

        return false === empty($out);
    }
}
