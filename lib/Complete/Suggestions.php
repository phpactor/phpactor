<?php

namespace Phpactor\Complete;

class Suggestions implements \IteratorAggregate
{
    private $suggestions = [];

    public function add(string $suggestion)
    {
        $this->suggestions[] = $suggestion;
    }

    public function merge(SuggestionCollection $suggestions)
    {
        $this->suggestions = array_merge(
            $this->suggestions,
            $suggestions->all()
        );
    }

    public function all()
    {
        return $this->suggestions;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->suggestions);
    }
}
