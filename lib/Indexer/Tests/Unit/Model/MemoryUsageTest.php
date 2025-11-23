<?php

namespace Phpactor\Indexer\Tests\Unit\Model;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Indexer\Model\MemoryUsage;

class MemoryUsageTest extends TestCase
{
    public function testMemoryLimit(): void
    {
        MemoryUsage::create()->memoryLimit();

        // the result is system dependent
        $this->addToAssertionCount(1);
    }

    public function testMemoryUsage(): void
    {
        self::assertIsInt(MemoryUsage::create()->memoryUsage());
    }

    #[DataProvider('provideFormat')]
    public function testFormat(string $limit, int $usage, string $expected): void
    {
        self::assertEquals($expected, MemoryUsage::createFromLimitAndUsage($limit, $usage)->memoryUsageFormatted());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideFormat(): Generator
    {
        yield 'infinite memory' => [
            '-1',
            0,
            '0/∞ mb'
        ];

        yield [
            '1048576',
            0,
            '0/1 mb'
        ];

        yield [
            '1000000',
            1000000,
            '1/1 mb'
        ];

        yield [
            '1000K',
            1000000,
            '1/1 mb'
        ];

        yield [
            '1M',
            1000000,
            '1/1 mb'
        ];

        yield [
            '100M',
            1000000,
            '1/100 mb'
        ];

        yield [
            '1G',
            1000000,
            '1/1,000 mb'
        ];
    }
}
