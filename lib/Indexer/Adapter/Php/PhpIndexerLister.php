<?php

namespace Phpactor\Indexer\Adapter\Php;

use Generator;
use Phpactor\Indexer\Model\IndexInfo;
use Phpactor\Indexer\Model\IndexLister;
use SplFileInfo;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;

class PhpIndexerLister implements IndexLister
{
    public function __construct(private string $indexDirectory)
    {
    }
    public function list(): Generator
    {
        $indexes = array_filter(array_map(
            fn (string $name) => Path::join($this->indexDirectory, $name),
            array_filter(scandir($this->indexDirectory), fn (string $name) => !in_array($name, ['.', '..'])),
        ), fn (string $indexPath) => is_dir($indexPath));

        foreach ($indexes as $indexPath) {
            $info = IndexInfo::fromSplFileInfo(new SplFileInfo($indexPath));
            // warmup the size
            $info->size();
            yield $info;
        }
    }
}
