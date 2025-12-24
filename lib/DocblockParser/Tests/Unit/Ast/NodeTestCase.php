<?php

namespace Phpactor\DocblockParser\Tests\Unit\Ast;

use PHPUnit\Framework\Attributes\DataProvider;
use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\DocblockParser\Ast\Element;
use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Lexer;
use Phpactor\DocblockParser\Parser;

class NodeTestCase extends TestCase
{
    #[DataProvider('provideNode')]
    public function testNode(string $doc, ?Closure $assertion = null): void
    {
        $node = $this->parse($doc);
        $nodes = iterator_to_array($node->selfAndDescendantElements(), false);
        self::assertIsIterable($nodes);
        self::assertEquals(0, $node->start(), 'Start offset');
        self::assertEquals(strlen($doc), $node->end(), 'End offset');
        self::assertGreaterThanOrEqual(0, $node->length(), 'Length is negative');

        if ($assertion) {
            $assertion($node);
        }
    }

    #[DataProvider('provideNode')]
    public function testPartialParse(string $doc): void
    {
        $node = $this->parse($doc);
        $partial = [];
        foreach ($node->children() as $child) {
            $partial[] = $child->toString();
            $node = $this->parse(implode(' ', $partial));
            self::assertInstanceOf(Element::class, $node);
        }
    }

    #[DataProvider('provideNode')]
    public function testIsomorphism(string $doc): void
    {
        $one = $this->parse($doc);
        $two = $this->parse($one->toString());
        self::assertEquals($one, $two, $one->toString());
    }

    private function parse(string $doc): Node
    {
        $node = (new TolerantAstProvider())->parse((new Lexer())->lex($doc));
        return $node;
    }
}
