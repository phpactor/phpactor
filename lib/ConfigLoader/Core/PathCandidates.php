<?php

namespace Phpactor\ConfigLoader\Core;

use IteratorAggregate;
use Traversable;

class PathCandidates implements IteratorAggregate
{
    /**
     * @var PathCandidate[]
     */
    private array $candidates = [];

    public function __construct(array $candidates)
    {
        foreach ($candidates as $candidate) {
            $this->add($candidate);
        }
    }

    
    public function getIterator(): Traversable
    {
        foreach ($this->candidates as $candidate) {
            yield $candidate;
        }
    }

    private function add(PathCandidate $candidate): void
    {
        $this->candidates[] = $candidate;
    }
}
