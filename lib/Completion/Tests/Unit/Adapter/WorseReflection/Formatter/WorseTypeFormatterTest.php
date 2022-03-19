<?php

namespace Phpactor\Completion\Tests\Unit\Adapter\WorseReflection\Formatter;

use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypeFormatter;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypesFormatter;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\ReflectorBuilder;

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
        $reflector = ReflectorBuilder::create()->build();
        yield 'no types' => [
            Types::empty(),
            '<unknown>',
        ];

        yield 'single scalar' => [
            Types::fromTypes([TypeFactory::string()]),
            'string',
        ];

        yield 'union' => [
            Types::fromTypes([TypeFactory::string(), TypeFactory::null()]),
            'string|null',
        ];

        yield 'typed array' => [
            Types::fromTypes([TypeFactory::array('string')]),
            'string[]',
        ];

        yield 'generic' => [
            Types::fromTypes([TypeFactory::collection($reflector, 'Collection', 'Item')]),
            'Collection<Item>',
        ];
    }
}
