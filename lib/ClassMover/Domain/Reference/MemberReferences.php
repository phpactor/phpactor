<?php

namespace Phpactor\ClassMover\Domain\Reference;

use IteratorAggregate;
use Countable;
use ArrayIterator;

final class MemberReferences implements IteratorAggregate, Countable
{
    private $methodReferences = [];

    private function __construct($methodReferences)
    {
        foreach ($methodReferences as $item) {
            $this->add($item);
        }
    }

    public static function fromMemberReferences(array $methodReferences): MemberReferences
    {
        return new self($methodReferences);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->methodReferences);
    }

    public function withClasses(): MemberReferences
    {
        return self::fromMemberReferences(array_filter($this->methodReferences, function (MemberReference $reference) {
            return $reference->hasClass();
        }));
    }

    public function withoutClasses(): MemberReferences
    {
        return self::fromMemberReferences(array_filter($this->methodReferences, function (MemberReference $reference) {
            return false === $reference->hasClass();
        }));
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->methodReferences);
    }

    public function unique(): self
    {
        $members = [];
        return self::fromMemberReferences(array_filter($this->methodReferences, function (MemberReference $reference) use (&$members) {
            $hash = sprintf('%s.%s.%s', $reference->methodName(), $reference->position()->start(), $reference->position()->end());
            $inArray = false === in_array($hash, $members);
            $members[] = $hash;
            return $inArray;
        }));
    }

    private function add(MemberReference $item): void
    {
        $this->methodReferences[] = $item;
    }
}
