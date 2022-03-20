<?php

namespace Phpactor\WorseReflection\Tests\Unit\DocblockParser\Ast;

use Generator;
use Phpactor\WorseReflection\DocblockParser\Ast\Docblock;
use Phpactor\WorseReflection\DocblockParser\Ast\Tag\DeprecatedTag;
use Phpactor\WorseReflection\DocblockParser\Ast\Tag\MethodTag;
use Phpactor\WorseReflection\DocblockParser\Ast\Tag\PropertyTag;
use Phpactor\WorseReflection\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ClassNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\GenericNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ListNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ScalarNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\UnionNode;
use Prophecy\Doubler\Generator\Node\MethodNode;

class NodeTest extends NodeTestCase
{
    /**
     * @return Generator<mixed>
     */
    public function provideNode(): Generator
    {
        yield from $this->provideApiTest();
        yield from $this->provideDocblock();
        yield from $this->provideTags();
        yield from $this->provideTypes();
    }

    /**
     * @return Generator<mixed>
     */
    private function provideApiTest(): Generator
    {
        yield [
            '@method static Baz\Bar bar(string $boo, string $baz)',
            function (MethodTag $methodNode): void {
                self::assertTrue($methodNode->hasChild(ClassNode::class));
                self::assertFalse($methodNode->hasChild(MethodTag::class));
                self::assertCount(7, iterator_to_array($methodNode->children()));
                self::assertCount(1, iterator_to_array($methodNode->children(ClassNode::class)));
                self::assertTrue($methodNode->hasDescendant(ScalarNode::class));
                self::assertFalse($methodNode->hasDescendant(MethodNode::class));
                self::assertCount(2, iterator_to_array($methodNode->descendantElements(ScalarNode::class)));
                self::assertInstanceOf(ScalarNode::class, $methodNode->firstDescendant(ScalarNode::class));
            }
        ];
    }

    /**
     * @return Generator<mixed>
     */
    private function provideTags()
    {
        yield [
            '@method static Baz\Bar bar(string $boo, string $baz)',
            function (MethodTag $methodNode): void {
                self::assertEquals('@method static Baz\Bar bar(string $boo, string $baz)', $methodNode->toString());
                self::assertEquals('string $boo, string $baz', $methodNode->parameters->toString());
                self::assertEquals('static', $methodNode->static->value);
                self::assertEquals('Baz\Bar', $methodNode->type->toString());
                self::assertEquals('bar', $methodNode->name->toString());
                self::assertEquals('(', $methodNode->parenOpen->toString());
                self::assertEquals(')', $methodNode->parenClose->toString());
                self::assertTrue($methodNode->hasChild(ClassNode::class));
                self::assertFalse($methodNode->hasChild(MethodTag::class));
            }
        ];
        yield [
            '@property Baz\Bar $foobar',
            function (PropertyTag $property): void {
                self::assertEquals('$foobar', $property->name->toString());
            }
        ];

        yield [ '@deprecated This is deprecated'];
        yield 'deprecated' => [
            '/** @deprecated This is deprecated */',
            function (Docblock $block): void {
                self::assertTrue($block->hasTag(DeprecatedTag::class));
            }
        ];

        yield [ '/** This is docblock @deprecated Foo */'];
        yield [ '@mixin Foo\Bar'];
        yield [ '@param string $foo This is a parameter'];
        yield ['@param Baz\Bar $foobar This is a parameter'];
        yield ['@var Baz\Bar $foobar'];
        yield ['@return Baz\Bar'];
        yield ['@return $this'];
    }

    /**
     * @return Generator<mixed>
     */
    private function provideTypes(): Generator
    {
        yield 'scalar' => ['string'];
        yield 'union' => [
            '@return string|int|bool|float|mixed',
            function (ReturnTag $return): void {
                $type = $return->type;
                assert($type instanceof UnionNode);
                self::assertInstanceOf(UnionNode::class, $type);
                self::assertEquals('string', $type->types->types()->first()->toString());
                self::assertCount(5, $type->types->types());
            }
        ];
        yield 'list' => [
            '@return Foo[]',
            function (ReturnTag $return): void {
                self::assertInstanceOf(ListNode::class, $return->type);
            }
        ];
        yield 'generic' => [
            '@return Foo<Bar<string, int>, Baz|Bar>',
            function (ReturnTag $return): void {
                self::assertInstanceOf(GenericNode::class, $return->type);
            }
        ];
    }

    private function provideDocblock()
    {
        yield 'docblock' => [
            <<<'EOT'
                /**
                 * This is a docblock
                 * With some text - 
                 * and maybe some 
                 * ```
                 * Markdown
                 * ```
                 * @param This $should not be included
                 */
                EOT
            , function (Docblock $docblock): void {
                self::assertEquals(<<<'EOT'
                    This is a docblock
                    With some text - 
                    and maybe some 
                    ```
                    Markdown
                    ```
                    EOT
, $docblock->prose());
            }
        ];
    }
}
