<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\Qualifier;

use PHPUnit\Framework\Attributes\DataProvider;
use Closure;
use Microsoft\PhpParser\Parser;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TestUtils\ExtractOffset;

abstract class TolerantQualifierTestCase extends TestCase
{
    #[DataProvider('provideCouldComplete')]
    public function testCouldComplete(string $source, Closure $assertion): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $parser = new Parser();
        $root = $parser->parseSourceFile($source);
        $node = $root->getDescendantNodeAtPosition($offset);

        $assertion($this->createQualifier()->couldComplete($node));
    }

    abstract public function createQualifier(): TolerantQualifier;
}
