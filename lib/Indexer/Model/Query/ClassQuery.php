<?php

namespace Phpactor\Indexer\Model\Query;

use Generator;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\IndexQuery;
use Phpactor\Indexer\Model\LocationConfidence;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\Indexer\Model\Record\FileRecord;
use Phpactor\TextDocument\Location;

class ClassQuery implements IndexQuery
{
    public function __construct(private Index $index)
    {
    }

    public function get(string $identifier): ?ClassRecord
    {
        $prototype = ClassRecord::fromName($identifier);
        return $this->index->has($prototype) ? $this->index->get($prototype) : null;
    }

    /**
     * @return array<FullyQualifiedName>
     */
    public function implementing(string $name): array
    {
        $record = $this->index->get(ClassRecord::fromName($name));
        assert($record instanceof ClassRecord);

        return array_map(function (string $fqn) {
            return FullyQualifiedName::fromString($fqn);
        }, $record->implementations());
    }

    /**
     * @return Generator<FullyQualifiedName>
     */
    public function subClasses(string $className): Generator
    {
        $record = $this->index->get(ClassRecord::fromName($className));
        assert($record instanceof ClassRecord);

        foreach($record->implements() as $fqn) {
            yield FullyQualifiedName::fromString($fqn);
        }
    }

    /**
     * @return Generator<LocationConfidence>
     */
    public function referencesTo(string $identifier): Generator
    {
        $record = $this->index->get(ClassRecord::fromName($identifier));
        assert($record instanceof ClassRecord);

        foreach ($record->references() as $fileReference) {
            $fileRecord = $this->index->get(FileRecord::fromPath($fileReference));
            assert($fileRecord instanceof FileRecord);

            foreach ($fileRecord->references()->to($record) as $classReference) {
                yield LocationConfidence::surely(
                    Location::fromPathAndOffsets(
                        $fileRecord->filePath() ?? '',
                        $classReference->start(),
                        $classReference->end(),
                    )
                );
            }
        }
    }
}
