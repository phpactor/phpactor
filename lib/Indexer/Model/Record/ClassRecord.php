<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record;

final class ClassRecord implements Record, HasFileReferences, HasPath, HasFullyQualifiedName
{
    use FullyQualifiedReferenceTrait;
    use HasFileReferencesTrait;
    use HasPathTrait;
    public const RECORD_TYPE = 'class';
    public const TYPE_CLASS = 'class';
    public const TYPE_INTERFACE = 'interface';
    public const TYPE_TRAIT = 'trait';
    public const TYPE_ENUM = 'enum';
    public const FLAG_ATTRIBUTE = 1;

    /**
     * @var array<string>
     */
    private array $implementations = [];

    /**
     * @var array<string>
     */
    private array $implements = [];

    /**
     * Type of "class": class, interface or trait, etc
     */
    private ?string $type = null;

    private int $flags = 0;

    public static function fromName(string $name): self
    {
        return new self($name);
    }

    public function clearImplemented(): void
    {
        $this->implements = [];
    }

    public function addImplementation(FullyQualifiedName $fqn): void
    {
        $this->implementations[(string)$fqn] = (string)$fqn;
    }

    public function addImplements(FullyQualifiedName $fqn): void
    {
        $this->implements[(string)$fqn] = (string)$fqn;
    }

    public function removeClass(FullyQualifiedName $implementedClass): void
    {
        foreach ($this->implementations as $key => $implementation) {
            if ($implementation !== $implementedClass->__toString()) {
                continue;
            }

            unset($this->implementations[$key]);
        }
    }

    public function removeImplementation(FullyQualifiedName $name): bool
    {
        if (!isset($this->implementations[(string)$name])) {
            return false;
        }
        unset($this->implementations[(string)$name]);
        return true;
    }

    /**
     * @return array<string>
     */
    public function implementations(): array
    {
        return $this->implementations;
    }

    /**
     * @return array<string>
     */
    public function implements(): array
    {
        return $this->implements;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }


    public function recordType(): string
    {
        return self::RECORD_TYPE;
    }

    public function withType(?string $type): ClassRecord
    {
        $clone = clone $this;
        $clone->type = $type;
        return $clone;
    }

    public function setFlags(int $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    public function addFlag(int $flag): self
    {
        $this->flags = $this->flags | $flag;

        return $this;
    }

    public function hasFlag(int $flag): bool
    {
        return (bool) ($this->flags & $flag);
    }
}
