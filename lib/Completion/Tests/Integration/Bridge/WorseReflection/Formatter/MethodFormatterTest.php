<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Formatter;

use Generator;
use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class MethodFormatterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideFormatConstant
     */
    public function testFormatsConstant(string $code, string $expected): void
    {
        $code = TextDocumentBuilder::fromUnknown($code);
        $constant = ReflectorBuilder::create()->build()->reflectClassLikesIn(
            $code
        )->first()->methods()->first();

        self::assertTrue($this->formatter()->canFormat($constant));
        self::assertEquals($expected, $this->formatter()->format($constant));
    }

    /**
     * @return Generator<array{string,string}>
     */
    public function provideFormatConstant(): Generator
    {
        yield [
            '<?php class Foobar {public function barfoo()}',
            'pub barfoo()',
        ];

        yield [
            '<?php class Foobar {/** @deprecated */public function barfoo()}',
            '⚠ pub barfoo()'
        ];
    }
}
