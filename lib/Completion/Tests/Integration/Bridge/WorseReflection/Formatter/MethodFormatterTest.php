<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Formatter;

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
        $constant = ReflectorBuilder::create()->build()->reflectClassesIn(
            $code
        )->first()->methods()->first();

        self::assertTrue($this->formatter()->canFormat($constant));
        self::assertEquals($expected, $this->formatter()->format($constant));
    }

    public function provideFormatConstant()
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
