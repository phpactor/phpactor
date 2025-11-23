<?php

namespace Phpactor\Completion\Tests\Unit\Core\LabelFormatter;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\LabelFormatter\HelpfulLabelFormatter;

class HelpfulLabelFormatterTest extends TestCase
{
    /**
     * @param array<string,bool> $seen
     */
    #[DataProvider('provideFormat')]
    public function testFormat(string $name, array $seen, string $expected): void
    {
        $formatter = new HelpfulLabelFormatter();
        self::assertEquals($expected, $formatter->format($name, $seen));
    }

    /**
     * @return Generator<array{string,array<string,bool>,string}>
     */
    public static function provideFormat(): Generator
    {
        yield [
            'Request',
            [],
            'Request'
        ];
        yield [
            'Request',
            [
                'Request' => true,
            ],
            'Request'
        ];
        yield [
            'Foo\Request',
            [
                'Request' => true,
            ],
            'Request (Foo)'
        ];
        yield [
            'PhpParser\Node',
            [
                'Node' => true,
                'Node (Microsoft)' => true,
                'Node (Phpactor)' => true,
            ],
            'Node (PhpParser)'
        ];
        yield [
            'Foo\Bar\Node',
            [
                'Node (Foo)' => true,
            ],
            'Node (Foo\Bar)'
        ];
        yield [
            'Foo\Bar\Baz\Node',
            [
                'Node (Foo)' => true,
                'Node (Foo\Bar)' => true,
            ],
            'Node (Foo\Bar\Baz)'
        ];
        yield 'invalid case for 2 identically named classes' => [
            'Foo\Bar\Node',
            [
                'Node (Foo)' => true,
                'Node (Foo\Bar)' => true,
            ],
            'Node'
        ];
    }
}
