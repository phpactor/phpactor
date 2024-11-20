<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\Record;

final class FunctionRecord implements HasFileReferences, HasPath, Record, HasFullyQualifiedName
{
    use FullyQualifiedReferenceTrait;
    use HasFileReferencesTrait;
    use HasPathTrait;

    public static function fromName(string $name): self
    {
        return new self($name);
    }

    public function recordType(): RecordType
    {
        return RecordType::FUNCTION;
    }
}
