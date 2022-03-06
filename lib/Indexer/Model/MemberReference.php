<?php

namespace Phpactor\Indexer\Model;

use Phpactor\Indexer\Model\Name\FullyQualifiedName;

class MemberReference
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var FullyQualifiedName|null
     */
    private $name;

    /**
     * @var string
     */
    private $memberName;

    public function __construct(string $type, ?FullyQualifiedName $name, string $memberName)
    {
        $this->type = $type;
        $this->name = $name;
        $this->memberName = $memberName;
    }

    public static function create(string $type, ?string $containerType, string $memberName): self
    {
        return new self($type, $containerType ? FullyQualifiedName::fromString($containerType) : null, $memberName);
    }

    /**
     * Member type, one of MemberRecord::TYPE_* (e.g. METHOD)
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
