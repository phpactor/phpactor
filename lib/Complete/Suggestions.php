<?php

namespace Phpactor\Complete;

use Phpactor\Complete\Suggestion;

class Suggestions implements \IteratorAggregate
{
    private $suggestions = [];

    public function add(Suggestion $suggestion)
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
