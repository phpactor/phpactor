<?php

namespace Phpactor\Completion\Tests\Benchmark\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\Completion\Tests\Benchmark\Bridge\TolerantParser\TolerantCompletorBenchCase;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseLocalVariableCompletor;

class WorseLocalVariableCompletorBench extends TolerantCompletorBenchCase
{
    protected function createTolerant(string $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        return new WorseLocalVariableCompletor($reflector, new ObjectFormatter());
    }
}
