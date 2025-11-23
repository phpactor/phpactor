<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use PHPUnit\Framework\Attributes\DataProvider;
use Exception;
use Generator;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantExtractExpression;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Tests\Adapter\TolerantParser\TolerantTestCase;

class TolerantExtractExpressionTest extends TolerantTestCase
{
    #[DataProvider('provideExtractExpression')]
    public function testExtractExpression(string $test, string $name, ?string $expectedExceptionMessage = null): void
    {
        [$source, $expected, $offsetStart, $offsetEnd] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        if ($expectedExceptionMessage) {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $extractMethod = new TolerantExtractExpression();

        $textEdits = $extractMethod->extractExpression(SourceCode::fromString($source), $offsetStart, $offsetEnd, $name);
        $transformed = $textEdits->apply($source);
        $this->assertEquals(trim($expected), trim($transformed));
    }

    public static function provideExtractExpression(): Generator
    {
        yield 'no op' => [
            'extractExpression1.test',
            'foobar',
        ];

        yield 'extract string literal' => [
            'extractExpression2.test',
            'foobar',
        ];

        yield 'extract on end position semi-colon: object creation' => [
            'extractExpression3.test',
            'foobar',
        ];

        yield 'single node' => [
            'extractExpression4.test',
            'foobar',
        ];

        yield 'single array expression' => [
            'extractExpression5.test',
            'foobar',
        ];

        yield 'stand-alone expression: whole expression' => [
            'extractExpression6.test',
            'foobar',
        ];

        yield 'stand-alone expression: partial expression' => [
            'extractExpression6A.test',
            'foobar',
        ];

        yield 'string concatenation' => [
            'extractExpression7.test',
            'foobar',
        ];

        yield 'preserve statement indentation: spaces' => [
            'extractExpression8.test',
            'foobar',
        ];

        yield 'preserve statement indentation: tabs' => [
            'extractExpression8A.test',
            'foobar',
        ];

        yield 'preserve statement indentation: tabs and comments' => [
            'extractExpression8B.test',
            'foobar',
        ];

        yield 'extract element in array' => [
            'extractExpression9.test',
            'foobar',
        ];

        yield 'should not: start on method definition' => [
            'extractExpression10.test',
            'foobar',
        ];

        yield 'should not: start and end in different methods' => [
            'extractExpression11.test',
            'foobar',
        ];

        yield 'should not: start and end in different expressions' => [
            'extractExpression12.test',
            'foobar',
        ];

        yield 'multiline expression' => [
            'extractExpression13.test',
            'foobar',
        ];

        yield 'should not: inside class member list' => [
            'extractExpression14.test',
            'foobar',
        ];

        yield 'should not: class declaration' => [
            'extractExpression15.test',
            'foobar',
        ];

        yield 'should not: on function declaration' => [
            'extractExpression16.test',
            'foobar',
        ];

        yield 'single assignment expression: method call without semi-colon' => [
            'extractExpression17.test',
            'foobar',
        ];

        yield 'single assignment expression: method call with semi-colon' => [
            'extractExpression17A.test',
            'foobar',
        ];
    }

    public function testWillNotExtractExpressionIfNoRange(): void
    {
        $extractMethod = new TolerantExtractExpression();

        self::assertFalse($extractMethod->canExtractExpression(
            SourceCode::fromString('<?php new Foobar();'),
            8,
            8,
        ));
        self::assertTrue($extractMethod->canExtractExpression(
            SourceCode::fromString('<?php new Foobar();'),
            8,
            9,
        ));
    }
}
