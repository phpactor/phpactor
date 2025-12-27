<?php

namespace Phpactor\Filesystem\Domain;

final class CopyReport
{
    private function __construct(
        private readonly FileList $srcFiles,
        private readonly FileList $destFiles
    ) {
    }

    public static function fromSrcAndDestFiles(FileList $srcFiles, FileList $destFiles): CopyReport
    {
        return new self($srcFiles, $destFiles);
    }

    public function srcFiles(): FileList
    {
        return $this->srcFiles;
    }

    public function destFiles(): FileList
    {
        return $this->destFiles;
    }
}
