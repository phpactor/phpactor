<?php

namespace Phpactor\Indexer\Model;

use Generator;

interface IndexJob
{
    /**
     * Yields null is used to indicate when the job has nothing to report,
     * so that it doesn't block, letting the controller move on to other tasks.
     *
     * @return Generator<string|null>
     */
    public function generator(): Generator;

    public function run(): void;

    public function size(): int;

    public function describe(): string;
}
