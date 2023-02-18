<?php

namespace Phpactor\ClassMover\Domain\Reference;

use IteratorAggregate;
use Countable;
use ArrayIterator;
use Traversable;

/**
 * @implements IteratorAggregate<MemberReference>
 */
final class MemberReferences implements IteratorAggregate, Countable
{
    /** @var array<MemberReference> */
    private array $methodReferences = [];

    /** @param array<MemberReference> $methodReferences */
    private function __construct(array $methodReferences)
    {
        foreach ($methodReferences as $item) {
            $this->add($item);
        }
    }

    /** @param array<MemberReference> $methodReferences */
    public static function fromMemberReferences(array $methodReferences): MemberReferences
    {
        return new self($methodReferences);
    }

    public function getIterator(): Traversable
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


    public function count(): int
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
