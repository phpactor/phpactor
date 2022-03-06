<?php

namespace Phpactor\Completion\Tests\Unit\Adapter\WorseReflection\Formatter;

use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypeFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypesFormatter;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;

class WorseTypeFormatterTest extends TestCase
{
    /**
     * @dataProvider provideFormat
     */
    public function testFormat(Types $types, string $expected): void
    {
        $formatter = new ObjectFormatter([
            new TypeFormatter(),
            new TypesFormatter(),
        ]);

        $this->assertEquals($expected, $formatter->format($types));
    }

    public function provideFormat()
    {
        yield 'no types' => [
            Types::empty(),
            '<unknown>',
        ];

        yield 'single scalar' => [
            Types::fromTypes([Type::string()]),
            'string',
        ];

        yield 'union' => [
            Types::fromTypes([Type::string(), Type::null()]),
            'string|null',
        ];

        yield 'typed array' => [
            Types::fromTypes([Type::array('string')]),
            'string[]',
        ];

        yield 'generic' => [
            Types::fromTypes([Type::collection('Collection', 'Item')]),
            'Collection<Item>',
        ];
    }
}
