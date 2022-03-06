<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\Record;

final class ConstantRecord implements HasPath, Record, HasFullyQualifiedName
{
    use FullyQualifiedReferenceTrait;
    use HasPathTrait;

    public const RECORD_TYPE = 'constant';

    public static function fromName(string $name): self
    {
        return new self($name);
    }

    /**
     * {@inheritDoc}
     */
    public function recordType(): string
    {
        return self::RECORD_TYPE;
    }
}
