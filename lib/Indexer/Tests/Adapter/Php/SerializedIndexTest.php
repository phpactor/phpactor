<?php

namespace Phpactor\Indexer\Tests\Adapter\Php;

use Phpactor\Indexer\Adapter\Php\Serialized\FileRepository;
use Phpactor\Indexer\Adapter\Php\Serialized\SerializedIndex;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\RecordSerializer\PhpSerializer;
use Phpactor\Indexer\Tests\Adapter\IndexTestCase;

class SerializedIndexTest extends IndexTestCase
{
    protected function createIndex(): Index
    {
        return new SerializedIndex(new FileRepository(
            $this->workspace()->path('cache'),
            new PhpSerializer()
        ));
    }
}
