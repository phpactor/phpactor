<?php

namespace Phpactor\Filesystem\Domain;

interface FileListProvider
{
    public function fileList(): FileList;
}
