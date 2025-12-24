<?php

namespace Phpactor\Extension\LanguageServerInlineValue\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerInlineValue\Handler\InlineValueHandler;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\InlineValueVariableLookup;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\TestUtils\ExtractOffset;
use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffsetRange;

class InlineValueHandlerTest extends TestCase
{
    private const PATH = 'file:///hello';

    #[DataProvider('provideInlineValue')]
    public function testInlineValue(string $test): void
    {
        $ranges = [];
        $text = $test;
        // @phpstan-ignore booleanAnd.leftAlwaysTrue
        while (([ $text, $start, $end ] = ExtractOffset::fromSource($text)) && ($end != 0)) {
            $ranges[] = [
                RangeConverter::toLspRange(ByteOffsetRange::fromInts($start, $end), $text),
                mb_substr($text, $start, $end-$start),
            ];
        }

        $tester = $this->createTester();
        $tester->textDocument()->open(self::PATH, $text);

        $response = $tester->requestAndWait('textDocument/inlineValue', [
            'textDocument' => new TextDocumentIdentifier(self::PATH),
            'range' => RangeConverter::toLspRange(ByteOffsetRange::fromInts(0, mb_strlen($text)), $text),
        ]);
        self::assertNotNull($response);
        $tester->assertSuccess($response);
        $result = $response->result;
        $this->assertIsArray($response->result);
        $this->assertCount(count($ranges), $response->result);
        foreach ($response->result as $result) {
            $this->assertNotEmpty($ranges);
            [$test_range, $test_text] = array_shift($ranges);
            $this->assertInstanceOf(InlineValueVariableLookup::class, $result);
            $this->assertEquals(true, $result->caseSensitiveLookup);
            $this->assertEquals($test_range, $result->range);
            $this->assertEquals($test_text, $result->variableName);
        }
    }

    public static function provideInlineValue(): Generator
    {
        yield 'var' => [
            <<<'EOF'
                <?php
                function func1(<>$param1<>, <>$param2<>) {
                    <>$param1<>;
                }
                EOF,
        ];
        yield 'foreach' => [
            <<<'EOF'
                <?php
                foreach (<>$array<> as <>$key<> => <>$val<>) {
                    <>$param1<>;
                }
                EOF,
        ];
    }

    protected function createTester(): LanguageServerTester
    {
        $tester = LanguageServerTesterBuilder::create();
        $tester->addHandler(new InlineValueHandler($tester->workspace(), new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider()));
        return $tester->build();
    }
}
