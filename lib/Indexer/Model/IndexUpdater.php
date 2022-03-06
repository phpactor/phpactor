<?php

namespace Phpactor\Indexer\Model;

interface IndexUpdater
{
    public function build(FileList $fileList): void;
}
