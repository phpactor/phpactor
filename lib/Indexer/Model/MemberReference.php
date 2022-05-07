<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\MemberRecord;

class MemberReference
{
    /**
     * @var MemberRecord::TYPE_*
     */
    private string $type;

    private ?FullyQualifiedName $name;

    private ?string $memberName;

    /**
     * @param MemberRecord::TYPE_* $type
     */
    public function __construct(string $type, ?FullyQualifiedName $name, string $memberName)
    {
        $this->type = $type;
        $this->name = $name;
        $this->memberName = $memberName;
    }

    /**
     * @param MemberRecord::TYPE_* $type
     */
    public static function create(string $type, ?string $containerType, string $memberName): self
    {
        return new self($type, $containerType ? FullyQualifiedName::fromString($containerType) : null, $memberName);
    }

    /**
     * Member type, one of MemberRecord::TYPE_* (e.g. METHOD)
     * @return MemberRecord::TYPE_*
     */
    public function type(): string
    {
        return $this->type;
    }

    public function containerType(): ?FullyQualifiedName
    {
        return $this->name;
    }

    public function memberName(): string
    {
        return $this->memberName;
    }
}
