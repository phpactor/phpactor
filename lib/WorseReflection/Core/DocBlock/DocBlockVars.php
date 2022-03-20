<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use IteratorAggregate;
use Phpactor\WorseReflection\Core\Types;
use ArrayIterator;

/**
 * @implements IteratorAggregate<DocBlockVar>
 */
class DocBlockVars implements IteratorAggregate
{
    private array $vars = [];

    public function __construct(array $vars)
    {
        foreach ($vars as $var) {
            $this->add($var);
        }
    }

    public function types(): Types
    {
        $types = [];
        foreach ($this->vars as $var) {
            foreach ($var->types() as $type) {
                $types[] = $type;
            }
        }

        return Types::fromTypes($types);
    }
    
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->vars);
    }

    private function add(DocBlockVar $var): void
    {
        $this->vars[] = $var;
    }
}
