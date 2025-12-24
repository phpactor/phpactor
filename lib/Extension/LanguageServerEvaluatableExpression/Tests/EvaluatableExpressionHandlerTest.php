<?php

namespace Phpactor\Extension\LanguageServerEvaluatableExpression\Tests;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerEvaluatableExpression\Handler\EvaluatableExpressionHandler;
use Phpactor\Extension\LanguageServerEvaluatableExpression\Protocol\EvaluatableExpression;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\TestUtils\ExtractOffset;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffset;

class EvaluatableExpressionHandlerTest extends TestCase
{
    private const PATH = 'file:///hello';

    #[DataProvider('provideEvaluatableExpression')]
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
        self::assertNotNull($response);
        $tester->assertSuccess($response);
        $result = $response->result;
        $this->assertInstanceOf(EvaluatableExpression::class, $result);
        $this->assertEquals($eval, $result->expression);
    }

    public static function provideEvaluatableExpression(): Generator
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

    protected function createTester(): LanguageServerTester
    {
        $tester = LanguageServerTesterBuilder::create();
        $tester->addHandler(new EvaluatableExpressionHandler($tester->workspace(), new TolerantAstProvider()));
        return $tester->build();
    }
}
