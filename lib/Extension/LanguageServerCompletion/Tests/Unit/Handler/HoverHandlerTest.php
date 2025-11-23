<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests\Unit\Handler;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Hover;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\Extension\LanguageServerCompletion\Tests\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;

class HoverHandlerTest extends IntegrationTestCase
{
    private const PATH = 'file:///hello';

    #[DataProvider('provideHover')]
    public function testHover(string $test): void
    {
        [ $text, $offset ] = ExtractOffset::fromSource($test);

        $tester = $this->createTester();
        $tester->textDocument()->open(self::PATH, $text);

        $response = $tester->requestAndWait('textDocument/hover', [
            'textDocument' => new TextDocumentIdentifier(self::PATH),
            'position' => PositionConverter::byteOffsetToPosition(ByteOffset::fromInt((int)$offset), $text)
        ]);
        $tester->assertSuccess($response);
        $result = $response->result;
        $this->assertInstanceOf(Hover::class, $result);
    }

    public static function provideHover(): Generator
    {
        yield 'var' => [
            '<?php $foo = "foo"; $f<>oo;',
        ];
        yield 'interface type' => [
            '<?php interface ThisInterface{}/** @var ThisInterface $foo */$f<>oo = "bar"',
        ];


        yield 'poperty' => [
            '<?php class A { private $<>b; }',
        ];

        yield 'method' => [
            '<?php class A { private function f<>oo():string {} }',
        ];

        yield 'method with documentation' => [
            <<<'EOT'
                <?php

                class A {
                    /**
                     * This is a method
                     */
                    private function f<>oo():string {}
                }
                EOT
            ,
        ];

        yield 'method with parent documentation' => [
            <<<'EOT'
                <?php

                class Foobar {
                    /**
                     * The original documentation
                     */
                    private function foo():string {}
                }
                class A extends Foobar {
                    /**
                     * This is a method
                     */
                    private function f<>oo():string {}
                }
                EOT
            ,
        ];

        yield 'method on a union' => [
            <<<'EOT'
                <?php

                class Barfoo {
                    /**
                     * The original documentation
                     */
                    private function foo():string {}
                }
                class Foobar {
                    /**
                     * The original documentation
                     */
                    private function foo():string {}
                }

                function foo(Barfoo|Foobar $foo) {
                    $foo->fo<>o();
                }

                EOT
            ,
        ];

        yield 'class' => [
            '<?php cl<>ass A { } }',
            'A'
        ];

        yield 'unknown function' => [
            '<?php foobar<>()'
        ];
    }
}
