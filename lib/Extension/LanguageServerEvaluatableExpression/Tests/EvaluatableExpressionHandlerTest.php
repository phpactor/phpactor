<?php

namespace Phpactor\Extension\LanguageServerEvaluatableExpression\Tests;

use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerEvaluatableExpression\Protocol\EvaluatableExpression;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\Extension\LanguageServerCompletion\Tests\IntegrationTestCase;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;

class EvaluatableExpressionHandlerTest extends IntegrationTestCase
{
    private const PATH = 'file:///hello';

    /**
     * @dataProvider provideEvaluatableExpression
     */
    public function testEvaluatableExpression(string $test): void
    {
        [ $text, $start, $offset ] = ExtractOffset::fromSource($test);
        [ $text, $end ] = ExtractOffset::fromSource($text);
        $eval = mb_substr($text, $start, $end-$start);

        $tester = $this->createTester();
        $tester->textDocument()->open(self::PATH, $text);

        $response = $tester->requestAndWait('textDocument/xevaluatableExpression', [
            'textDocument' => new TextDocumentIdentifier(self::PATH),
            'position' => PositionConverter::byteOffsetToPosition(ByteOffset::fromInt((int)$offset), $text)
        ]);
        $tester->assertSuccess($response);
        $result = $response->result;
        $this->assertInstanceOf(EvaluatableExpression::class, $result);
        $this->assertEquals($eval, $result->expression);
    }

    public function provideEvaluatableExpression(): Generator
    {
        yield 'var' => [
            '<?php $foo = "foo"; <>$f<>oo<>;',
        ];
        yield 'array' => [
            '<?php $foo = []; <>$f["aa<>a"]<>;',
        ];
        yield 'object array' => [
            '<?php if (<>$foo->abc["aa<>a"]<> == "test") {};',
        ];
        yield 'inner var' => [
            '<?php if ($foo->abc[<>$aa<>a<>] == "test") {};',
        ];
        yield 'just foo' => [
            '<?php if (<>$fo<>o<>->abc[$aaa] == "test") {};',
        ];
        yield 'arg' => [
            '<?php function test(Sometype <>$a<>rg<>) {};',
        ];
    }
}
