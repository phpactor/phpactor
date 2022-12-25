<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use IteratorAggregate;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use ArrayIterator;
use Traversable;

/**
 * @implements IteratorAggregate<DocBlockParam>
 */
class DocBlockParams implements IteratorAggregate
{
    /**
     * @param DocBlockParam[]
     */
    private array $params = [];

    /**
     * @param DocBlockParam[] $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $param) {
            $this->add($param);
        }
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->params);
    }

    private function add(DocBlockParam $param): void
    {
        $this->params[$param->name()] = $param;
    }

    public function has(string $name): bool
    {
        return isset($this->params[$name]);
    }
}
