<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\Qualifier;

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Closure;
use Phpactor\Completion\Bridge\TolerantParser\TolerantQualifier;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TestUtils\ExtractOffset;

abstract class TolerantQualifierTestCase extends TestCase
{
    #[DataProvider('provideCouldComplete')]
    public function testCouldComplete(string $source, Closure $assertion): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);

        $parser = new TolerantAstProvider();
        $root = $parser->get(TextDocumentBuilder::create($source)->build());
        $node = $root->getDescendantNodeAtPosition($offset);

        $assertion($this->createQualifier()->couldComplete($node));
    }

    abstract public function createQualifier(): TolerantQualifier;
}
