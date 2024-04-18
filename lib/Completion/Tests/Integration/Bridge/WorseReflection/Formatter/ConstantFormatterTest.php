<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Formatter;

use Generator;
use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class ConstantFormatterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideFormatConstant
     */
    public function testFormatsConstant(string $code, string $expected): void
    {
        $code = TextDocumentBuilder::fromUnknown($code);
        $constant = ReflectorBuilder::create()->build()->reflectClassLikesIn($code)->classes()->first()->constants()->first();

        self::assertTrue($this->formatter()->canFormat($constant));
        self::assertEquals($expected, $this->formatter()->format($constant));
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public function provideFormatConstant(): Generator
    {
        yield 'string' => [
            '<?php namespace Bar {class Foobar {const BAR = "FOO";}}',
            'BAR = "FOO"',
        ];

        yield 'int' => [
            '<?php namespace Bar {class Foobar {const BAR = 123;}}',
            'BAR = 123',
        ];

        yield 'invalid' => [
            '<?php namespace Bar {class Foobar {const BAR}}',
            'BAR = null',
        ];

        yield 'array' => [
            '<?php namespace Bar {class Foobar {const BAR=[123]}}',
            'BAR = [123]',
        ];
    }
}
