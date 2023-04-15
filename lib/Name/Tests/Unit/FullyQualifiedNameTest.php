<?php

namespace Phpactor\Name\Tests\Unit;

use Phpactor\Name\FullyQualifiedName;
use Generator;

class FullyQualifiedNameTest extends AbstractQualifiedNameTestCase
{
    /**
     * @dataProvider provideFullyQualified
     */
    public function testFullyQualified(string $className, bool $isFullyQualified): void
    {
        $this->assertEquals($isFullyQualified, FullyQualifiedName::fromString($className)->wasFullyQualified());
    }

    /**
     * @return Generator<array{string,bool}>
     */
    public function provideFullyQualified(): Generator
    {
        yield ['\\Fully\\Qualified', true];
        yield ['NotFully\\Qualified\\Class', false];
    }

    /** @param array<string> $parts */
    protected function createFromArray(array $parts): FullyQualifiedName
    {
        return FullyQualifiedName::fromArray($parts);
    }

    protected function createFromString(string $string): FullyQualifiedName
    {
        return FullyQualifiedName::fromString($string);
    }
}
