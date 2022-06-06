<?php

namespace Phpactor\Indexer\Model\Query;

use Generator;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexQuery;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\Indexer\Model\RecordReferenceEnhancer;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\TextDocument\Location;
use Phpactor\Indexer\Model\Record\MemberRecord;

class MemberQuery implements IndexQuery
{
    private Index $index;

    private RecordReferenceEnhancer $enhancer;

    public function __construct(Index $index, RecordReferenceEnhancer $enhancer)
    {
        $this->index = $index;
        $this->enhancer = $enhancer;
    }

    public function get(string $identifier): ?MemberRecord
    {
        if (!MemberRecord::isIdentifier($identifier)) {
            return null;
        }

        $prototype = MemberRecord::fromIdentifier($identifier);

        if (false === $this->index->has($prototype)) {
            return null;
        }

        return $this->index->get($prototype);
    }

    /**
     * @param MemberRecord::TYPE_* $type
     * @return Generator<LocationConfidence>
     */
    public function referencesTo(string $type, string $memberName, ?string $containerType = null): Generator
    {
        $record = $this->getByTypeAndName($type, $memberName);

        if (null === $record) {
            return;
        }

        assert($record instanceof MemberRecord);

        foreach ($record->references() as $fileReference) {
            $fileRecord = $this->index->get(FileRecord::fromPath($fileReference));
            assert($fileRecord instanceof FileRecord);
            if (!$fileRecord->filePath()) {
                continue;
            }
            dump($fileRecord->filePath());

            yield from $this->enhancer->enhance($fileRecord->filePath(), $containerType, $memberName);
        }
    }

    private function getByTypeAndName(string $type, string $name): ?MemberRecord
    {
        return $this->get($type . '#' . $name);
    }
}
