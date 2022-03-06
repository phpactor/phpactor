<?php

namespace Phpactor\Indexer\Model\Query;

use Generator;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexQuery;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\Indexer\Model\Record\FunctionRecord;
use Phpactor\TextDocument\Location;

class FunctionQuery implements IndexQuery
{
    /**
     * @var Index
     */
    private $index;

    public function __construct(Index $index)
    {
        $this->index = $index;
    }

    public function get(string $identifier): ?FunctionRecord
    {
        $prototype = FunctionRecord::fromName($identifier);
        return $this->index->has($prototype) ? $this->index->get($prototype) : null;
    }

    /**
     * @return Generator<LocationConfidence>
     */
    public function referencesTo(string $identifier): Generator
    {
        $record = $this->get($identifier);
        foreach ($record->references() as $fileReference) {
            $fileRecord = $this->index->get(FileRecord::fromPath($fileReference));
            assert($fileRecord instanceof FileRecord);

            foreach ($fileRecord->references()->to($record) as $functionReference) {
                yield LocationConfidence::surely(
                    Location::fromPathAndOffset($fileRecord->filePath(), $functionReference->offset())
                );
            }
        }
    }
}
