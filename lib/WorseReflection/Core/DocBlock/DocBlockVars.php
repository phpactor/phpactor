<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use IteratorAggregate;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use ArrayIterator;
use Traversable;

/**
 * @implements IteratorAggregate<DocBlockVar>
 */
class DocBlockVars implements IteratorAggregate
{
    /**
     * @var DocBlockVar[]
     */
    private array $vars = [];

    /**
     * @param DocBlockVar[] $vars
     */
    public function __construct(array $vars)
    {
        foreach ($vars as $var) {
            $this->add($var);
        }
    }

    public function type(): Type
    {
        foreach ($this->vars as $var) {
            return $var->type();
        }

        return TypeFactory::undefined();
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->vars);
    }

    private function add(DocBlockVar $var): void
    {
        $this->vars[] = $var;
    }
}
