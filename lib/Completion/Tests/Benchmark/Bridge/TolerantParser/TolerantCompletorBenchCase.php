<?php

namespace Phpactor\Completion\Tests\Benchmark\Bridge\TolerantParser;

use Phpactor\Completion\Bridge\TolerantParser\ChainTolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Tests\Benchmark\CompletorBenchCase;

abstract class TolerantCompletorBenchCase extends CompletorBenchCase
{
    abstract protected function createTolerant(string $source): TolerantCompletor;

    protected function create(string $source): Completor
    {
        return new ChainTolerantCompletor([
            $this->createTolerant($source)
        ]);
    }
}
