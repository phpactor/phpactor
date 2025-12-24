<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\TolerantParser\Parser;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\CachedAstProvider;
use Phpactor\WorseReflection\Core\Cache\TtlCache;

class CachedParserTest extends TestCase
{
    public function testCachesResults(): void
    {
        $parser = new CachedAstProvider(new TtlCache());
        $node1 = $parser->get(file_get_contents(__FILE__));
        $node2 = $parser->get(file_get_contents(__FILE__));

        $this->assertSame($node1, $node2);
    }

    public function testUsesUriInKey(): void
    {
        $parser = new CachedAstProvider(new TtlCache());
        $node1 = $parser->get(file_get_contents(__FILE__));
        $node2 = $parser->get(file_get_contents(__FILE__), 'file:///test.php');

        $this->assertNotSame($node1, $node2);
    }

    public function testReturnsDifferentResultsForDifferentSourceCodes(): void
    {
        $parser = new CachedAstProvider(new TtlCache());
        $node1 = $parser->get(file_get_contents(__FILE__));
        $node2 = $parser->get('Foobar' . file_get_contents(__FILE__));

        $this->assertNotSame($node1, $node2);
    }
}
