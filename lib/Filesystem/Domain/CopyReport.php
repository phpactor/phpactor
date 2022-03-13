<?php

namespace Phpactor\Filesystem\Domain;

final class CopyReport
{
    private $srcFiles;

    private $destFiles;

    private function __construct(FileList $srcFiles, FileList $destFiles)
    {
        $this->srcFiles = $srcFiles;
        $this->destFiles = $destFiles;
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
