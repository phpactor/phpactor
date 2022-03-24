<?php

namespace Phpactor\Filesystem\Domain;

use AppendIterator;

class ChainFileListProvider implements FileListProvider
{
    private $providers;

    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            $this->add($provider);
        }
    }

    public function fileList(): FileList
    {
        $iterator = new AppendIterator();
        foreach ($this->providers as $provider) {
            $iterator->append($provider->fileList()->getIterator());
        }

        return FileList::fromIterator($iterator);
    }

    private function add(FileListProvider $provider): void
    {
        $this->providers[] = $provider;
    }
}
