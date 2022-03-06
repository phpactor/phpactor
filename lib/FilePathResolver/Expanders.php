<?php

namespace Phpactor\FilePathResolver;

use ArrayIterator;
use IteratorAggregate;
use Phpactor\FilePathResolver\Exception\UnknownToken;

class Expanders implements IteratorAggregate
{
    /**
     * @var Expander[]
     */
    private $expanders = [];

    public function __construct(array $expanders)
    {
        foreach ($expanders as $expander) {
            $this->add($expander);
        }
    }

    public function toArray(): array
    {
        $array = [];
        foreach ($this->expanders as $expander) {
            $array[$expander->tokenName()] = $expander->replacementValue();
        }

        return $array;
    }

    public function get(string $tokenName)
    {
        if (!isset($this->expanders[$tokenName])) {
            throw new UnknownToken($tokenName, array_keys($this->expanders));
        }

        return $this->expanders[$tokenName];
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->expanders);
    }

    private function add(Expander $expander): void
    {
        $this->expanders[$expander->tokenName()] = $expander;
    }
}
