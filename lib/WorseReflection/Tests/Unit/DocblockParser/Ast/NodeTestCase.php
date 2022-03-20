<?php

namespace Phpactor\WorseReflection\Tests\Unit\DocblockParser\Ast;

use Closure;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\DocblockParser\Ast\Element;
use Phpactor\WorseReflection\DocblockParser\Ast\Node;
use Phpactor\WorseReflection\DocblockParser\Lexer;
use Phpactor\WorseReflection\DocblockParser\Parser;

class NodeTestCase extends TestCase
{
    /**
     * @dataProvider provideNode
     */
    public function testNode(string $doc, ?Closure $assertion = null): void
    {
        $node = $this->parse($doc);
        $nodes = iterator_to_array($node->selfAndDescendantElements(), false);
        self::assertIsIterable($nodes);
        self::assertEquals(0, $node->start(), 'Start offset');
        self::assertEquals(strlen($doc), $node->end(), 'End offset');

        if ($assertion) {
            $assertion($node);
        }
    }

    /**
     * @dataProvider provideNode
     */
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

    /**
     * @dataProvider provideNode
     */
    public function testIsomorphism(string $doc): void
    {
        $one = $this->parse($doc);
        $two = $this->parse($one->toString());
        self::assertEquals($one, $two, $one->toString());
    }

    private function parse(string $doc): Node
    {
        $node = (new Parser())->parse((new Lexer())->lex($doc));
        return $node;
    }
}
