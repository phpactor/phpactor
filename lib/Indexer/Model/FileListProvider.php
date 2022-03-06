<?php

namespace Phpactor\Indexer\Model;

interface FileListProvider
{
    public function provideFileList(Index $index, ?string $subPath = null): FileList;
}
