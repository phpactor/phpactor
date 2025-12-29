<?php

namespace Phpactor\Completion\Tests\Unit\Bridge\TolerantParser;

use Closure;
use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\CaseStatementNode;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\Completion\Bridge\TolerantParser\NodeAtCursorProvider;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\AstProvider\CachedAstProvider;

class NodeAtCursorProviderTest extends TestCase
{
    #[DataProvider('provideProvide')]
    public function testProvide(string $source, Closure $assertion): void
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $node = (new NodeAtCursorProvider(
            new TolerantAstProvider(),
        ))->get(
            TextDocumentBuilder::create($source)->build(),
            ByteOffset::fromInt($offset),
        );
        $assertion = $assertion->bindTo($this);
        $assertion($node);
    }

    public static function provideProvide(): Generator
    {
        yield [
            '<?php class Foo extends Bar implements One {    public function __c<> }',
            function (Node $node): void {
                self::assertInstanceOf(CompoundStatementNode::class, $node);
                self::assertInstanceOf(MethodDeclaration::class, $node->parent);
                self::assertSame(
                    '__c',
                    $node->parent->name?->getText((string)$node->getFileContents())
                );
            }
        ];
        yield [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function aaa()
                    {
                        $this->bb<>new Foobar();
                    }

                    public function bbb() {}
                    public function ccc() {}
                }

                EOT,
            function (Node $node): void {
                self::assertInstanceOf(MemberAccessExpression::class, $node);
                self::assertEquals('bb', $node->memberName->getText($node->getFileContents()));
            }
        ];
        yield [
            '<?php foobar($<>$bar)',
            function (Node $node): void {
                self::assertInstanceOf(Variable::class, $node);
            }
        ];

        yield [
            <<<'PHP'
                <?php
                class Foobar {
                    public function foo() {
                        while (true) {
                            $this->logger-><>
                            continue;
                        }
                        return 'foobar';
                    }
                }
                PHP,
            function (Node $node): void {
                self::assertInstanceOf(MemberAccessExpression::class, $node);
                self::assertEquals('', $node->memberName->getText($node->getFileContents()));
            }
        ];

        yield [
            <<<'PHP'
                    <?php $foobar
                        ->    <>
                PHP,
            function (Node $node): void {
                self::assertInstanceOf(MemberAccessExpression::class, $node);
            }
        ];

        yield [
            <<<'PHP'
                <?php namespace V; switch (true) { case 0: <> }
                PHP,
            function (Node $node): void {
                self::assertInstanceOf(CaseStatementNode::class, $node);
            }
        ];
    }

    public function testDoesNotCorruptOriginalAst(): void
    {
        $source = <<<'PHP'
            <?php
            $foo->he<>llo;
            PHP;

        [$source, $offset] = ExtractOffset::fromSource($source);
        $provider = new CachedAstProvider(new TolerantAstProvider());
        $document = TextDocumentBuilder::create($source)->build();

        $node = (new NodeAtCursorProvider($provider))->get(
            $document,
            ByteOffset::fromInt($offset),
        );

        self::assertInstanceOf(MemberAccessExpression::class, $node);

        self::assertEquals('he', $node->memberName->getText($source));

        $node = $provider->get($document)->getDescendantNodeAtPosition($offset);
        self::assertInstanceOf(MemberAccessExpression::class, $node);
        self::assertEquals('hello', $node->memberName->getText($source));
    }
}
