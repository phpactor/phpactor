<?php

namespace Phpactor\ConfigLoader\Core;

use IteratorAggregate;

class PathCandidates implements IteratorAggregate
{
    /**
     * @var PathCandidate[]
     */
    private $candidates = [];

    public function __construct(array $candidates)
    {
        foreach ($candidates as $candidate) {
            $this->add($candidate);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
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
