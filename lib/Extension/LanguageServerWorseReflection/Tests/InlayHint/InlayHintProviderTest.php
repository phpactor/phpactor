<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\Tests\InlayHint;

use Closure;
use Generator;
use Phpactor\Extension\LanguageServerWorseReflection\InlayHint\InlayHintProvider;
use Phpactor\Extension\LanguageServerWorseReflection\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

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
        $assertion((new InlayHintProvider(
            ReflectorBuilder::create()->addSource($source)->build()
        ))->inlayHints(TextDocumentBuilder::create($source)->build()));
    }

    public function provideInlayHintProvider(): Generator
    {
        yield 'inlay hint for member' => [
            '<?php class Foo{ function bar(string $bar): void {}} (new Foo())->bar("hello");',
            function (array $hints): void {
                dump($hints);
            }
        ];
    }
}
