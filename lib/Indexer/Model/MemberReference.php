<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\MemberRecordType;

class MemberReference
{
    public function __construct(
        private MemberRecordType $type,
        private ?FullyQualifiedName $name,
        private ?string $memberName,
    ) {
    }

    public static function create(MemberRecordType $type, ?string $containerType, string $memberName): self
    {
        return new self(
            $type,
            $containerType ? FullyQualifiedName::fromString($containerType) : null,
            $memberName,
        );
    }

    public function type(): MemberRecordType
    {
        return $this->type;
    }

    public function containerType(): ?FullyQualifiedName
    {
        return $this->name;
    }

    public function memberName(): ?string
    {
        return $this->memberName;
    }
}
