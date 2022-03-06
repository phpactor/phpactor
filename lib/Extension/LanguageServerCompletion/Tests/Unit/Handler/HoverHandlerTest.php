<?php

namespace Phpactor\Extension\LanguageServerHover\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Hover;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\Extension\LanguageServerCompletion\Tests\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;

class HoverHandlerTest extends IntegrationTestCase
{
    const PATH = 'file:///hello';

    /**
     * @dataProvider provideHover
     */
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

    public function provideHover()
    {
        yield 'var' => [
            '<?php $foo = "foo"; $f<>oo;',
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

        yield 'class' => [
            '<?php cl<>ass A { } }',
            'A'
        ];

        yield 'unknown function' => [
            '<?php foobar<>()'
        ];
    }
}
