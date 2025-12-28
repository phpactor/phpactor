<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Parser;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\AstProvider\CachedAstProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class CachedParserTest extends TestCase
{
    public function testCachesResults(): void
    {
        $parser = $this->createParser();
        $node1 = $parser->get(TextDocumentBuilder::create(file_get_contents(__FILE__))->build());
        $node2 = $parser->get(TextDocumentBuilder::create(file_get_contents(__FILE__))->build());

        $this->assertSame($node1, $node2);
    }

    public function testUsesUriInKey(): void
    {
        $parser = $this->createParser();
        $node1 = $parser->get(TextDocumentBuilder::create(file_get_contents(__FILE__))->build());
        $node2 = $parser->get(TextDocumentBuilder::fromUri(__FILE__)->build());

        $this->assertNotSame($node1, $node2);
    }

    public function testReturnsDifferentResultsForDifferentSourceCodes(): void
    {
        $parser = $this->createParser();
        $node1 = $parser->get(TextDocumentBuilder::create(file_get_contents(__FILE__))->build());
        $node2 = $parser->get(TextDocumentBuilder::create('Foobar' . file_get_contents(__FILE__))->build());

        $this->assertNotSame($node1, $node2);
    }

    private function createParser(): CachedAstProvider
    {
        return new CachedAstProvider(
            new TolerantAstProvider(),
            new TtlCache()
        );
    }
}
