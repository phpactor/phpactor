<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use AppendIterator;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use RuntimeException;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Visibility;
use Traversable;

/**
 * @template T of ReflectionMemberCollection
 * @implements ReflectionMemberCollection<ReflectionMember>
 */
class ChainReflectionMemberCollection implements ReflectionMemberCollection
{
    /**
     * @var array<T>
     */
    private array $collections = [];

    /**
     * @param array<T> $collections
     */
    public function __construct(array $collections)
    {
        foreach ($collections as $collection) {
            $this->add($collection);
        }
    }

    /**
     * @param array<T> $collections
     * @return self<T>
     */
    public static function fromCollections(array $collections): self
    {
        return new self($collections);
    }

    /**
     * @return AppendIterator<ReflectionMember>
     */
    public function getIterator(): Traversable
    {
        $iterator = new AppendIterator();
        foreach ($this->collections as $collection) {
            /** @phpstan-ignore-next-line */
            $iterator->append($collection->getIterator());
        }

        return $iterator;
    }

    public function count(): int
    {
        return array_reduce($this->collections, function ($acc, ReflectionMemberCollection $collection) {
            $acc += count($collection);
            return $acc;
        }, 0);
    }

    public function keys(): array
    {
        return array_reduce($this->collections, function ($acc, ReflectionMemberCollection $collection) {
            $acc = array_merge($acc, $collection->keys());
            return $acc;
        }, []);
    }

    /**
     * @return self<T>
     * @param T $collection
     */
    public function merge(ReflectionCollection $collection): self
    {
        $new = new self($this->collections);
        $new->add($collection);
        return $new;
    }

    public function get(string $name)
    {
        $known = [];
        foreach ($this->collections as $collection) {
            $known = array_merge($known, $collection->keys());
            if ($collection->has($name)) {
                return $collection->get($name);
            }
        }

        throw new ItemNotFound(sprintf(
            'Unknown item "%s", known items: "%s"',
            $name,
            implode('", "', $known)
        ));
    }

    public function first()
    {
        foreach ($this->collections as $collection) {
            return $collection->first();
        }

        throw new ItemNotFound(
            'None of the collections have items'
        );
    }

    public function last()
    {
        $last = null;

        foreach ($this->collections as $collection) {
            $last = $collection->last();
        }

        if ($last) {
            return $last;
        }

        throw new ItemNotFound(
            'None of the collections have items'
        );
    }

    public function has(string $name): bool
    {
        foreach ($this->collections as $collection) {
            if ($collection->has($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     * @param array<Visibility> $visibilities
     */
    public function byVisibilities(array $visibilities): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->byVisibilities($visibilities);
        }

        return new self($collections);
    }

    public function belongingTo(ClassName $class): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->belongingTo($class);
        }

        return new self($collections);
    }

    public function atOffset(int $offset): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->atOffset($offset);
        }

        return new self($collections);
    }

    public function byName(string $name): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->byName($name);
        }

        return new self($collections);
    }

    public function virtual(): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->virtual();
        }

        return new self($collections);
    }

    public function real(): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->real();
        }

        return new self($collections);
    }

    public function methods(): ReflectionMethodCollection
    {
        throw new RuntimeException(
            'Method not supported on chain member collection corrently'
        );
    }

    public function properties(): ReflectionPropertyCollection
    {
        throw new RuntimeException(
            'Method not supported on chain member collection corrently'
        );
    }
    
    /**
     * @param ReflectionMember::TYPE_* $type
     */
    public function byMemberType(string $type): ReflectionMemberCollection
    {
        $collections = [];
        foreach ($this->collections as $collection) {
            $collections[] = $collection->byMemberType($type);
        }

        return new self($collections);
    }

    /**
     * @param T $collection
     */
    private function add(ReflectionMemberCollection $collection): void
    {
        $this->collections[] = $collection;
    }
}
