<?php

namespace Phpactor\Completion\Tests\Unit\Adapter\WorseReflection\Formatter;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Completion\Bridge\WorseReflection\Formatter\TypeFormatter;
use Phpactor\Completion\Tests\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseTypeFormatterTest extends TestCase
{
    #[DataProvider('provideFormat')]
    public function testFormat(Type $type, string $expected): void
    {
        $formatter = new ObjectFormatter([
            new TypeFormatter(),
        ]);

        $this->assertEquals($expected, $formatter->format($type));
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideFormat(): Generator
    {
        $reflector = ReflectorBuilder::create()->build();
        yield 'no types' => [
            TypeFactory::unknown(),
            '<missing>',
        ];

        yield 'single scalar' => [
            TypeFactory::string(),
            'string',
        ];

        yield 'union' => [
            TypeFactory::union(TypeFactory::string(), TypeFactory::null()),
            'string|null',
        ];

        yield 'typed array' => [
            TypeFactory::array(TypeFactory::string()),
            'string[]',
        ];

        yield 'generic' => [
            TypeFactory::collection($reflector, 'Collection', 'Item'),
            'Collection<Item>',
        ];
    }
}
