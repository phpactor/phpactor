<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\WorseReflection\Formatter;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Completion\Tests\Integration\IntegrationTestCase;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class ConstantFormatterTest extends IntegrationTestCase
{
    #[DataProvider('provideFormatConstant')]
    public function testFormatsConstant(string $code, string $expected): void
    {
        $code = TextDocumentBuilder::fromUnknown($code);
        $constant = ReflectorBuilder::create()->build()->reflectClassesIn($code)->classes()->first()->constants()->first();

        self::assertTrue($this->formatter()->canFormat($constant));
        self::assertEquals($expected, $this->formatter()->format($constant));
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public static function provideFormatConstant(): Generator
    {
        yield 'string' => [
            '<?php namespace Bar {class Foobar {const BAR = "FOO";}}',
            'BAR = "FOO"',
        ];

        yield 'int' => [
            '<?php namespace Bar {class Foobar {const BAR = 123;}}',
            'BAR = 123',
        ];

        yield 'array' => [
            '<?php namespace Bar {class Foobar {const BAR=[123]}}',
            'BAR = [123]',
        ];
    }
}
