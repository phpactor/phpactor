<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Tests\InlayHint;

use Closure;
use Generator;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintOptions;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintProvider;
use Phpactor\Extension\LanguageServerWorseReflection\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;
use function Amp\Promise\wait;

class InlayHintProviderTest extends IntegrationTestCase
{
    /**
     * @param Closure(iterable<InlayHint>): void $assertion
     * @dataProvider provideInlayHintProvider
     */
    public function testInlayHintProvider(
        string $source,
        Closure $assertion
    ): void {
        $hints = wait((new InlayHintProvider(
            ReflectorBuilder::create()->addSource($source)->build(),
            new InlayHintOptions(true, true),
        ))->inlayHints(
            TextDocumentBuilder::create(
                $source
            )->build(),
            ByteOffsetRange::fromInts(
                0,
                strlen($source)
            )
        ));
        $assertion($hints);
    }

    /**
     * @return Generator<string,array{string,Closure(list<InlayHint>): void}>
     */
    public function provideInlayHintProvider(): Generator
    {
        yield 'inlay hint for member' => [
            '<?php class Foo{ function bar(string $bar): void {}} (new Foo())->bar("hello");',
            function (array $hints): void {
                self::assertCount(1, $hints);
            }
        ];
        yield 'inlay hint for variable' => [
            '<?php $foo = "foo"; $foo;',
            function (array $hints): void {
                self::assertCount(2, $hints);
            }
        ];
    }
}
