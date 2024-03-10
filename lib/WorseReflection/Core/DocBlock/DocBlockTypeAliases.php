<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use IteratorAggregate;
use ArrayIterator;
use Phpactor\WorseReflection\Core\Type;
use Traversable;

/**
 * @implements IteratorAggregate<DocBlockTypeAlias>
 */
class DocBlockTypeAliases implements IteratorAggregate
{
    /**
     * @var array<string,DocBlockTypeAlias>
     */
    private array $aliases = [];

    /**
     * @param list<DocBlockTypeAlias> $aliases
     */
    public function __construct(array $aliases)
    {
        foreach ($aliases as $alias) {
            $this->aliases[$alias->alias()] = $alias;
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->aliases);
    }

    public function forType(Type $type): ?Type
    {
        if (isset($this->aliases[$type->toPhpString()])) {
            return $this->aliases[$type->toPhpString()]->type();
        }
        return null;
    }
}
