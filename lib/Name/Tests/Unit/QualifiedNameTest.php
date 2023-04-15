<?php

namespace Phpactor\Name\Tests\Unit;

use Generator;
use Phpactor\Name\Exception\InvalidName;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\Name\QualifiedName;

class QualifiedNameTest extends AbstractQualifiedNameTestCase
{
    public function testCanBeConvertedToFullyQualifiedName(): void
    {
        $this->assertEquals(
            FullyQualifiedName::fromString('Foobar\\Barfoo'),
            $this->createFromString('Foobar\\Barfoo')->toFullyQualifiedName()
        );
    }

    /**
     * @dataProvider provideFullyQualified
     */
    public function testFullyQualified(string $className, bool $isFullyQualified): void
    {
        $this->assertEquals($isFullyQualified, QualifiedName::fromString($className)->wasFullyQualified());
    }

    /**
     * @return Generator<array{string,bool}>
     */
    public function provideFullyQualified(): Generator
    {
        yield ['\\Fully\\Qualified', true];
        yield ['NotFully\\Qualified\\Class', false];
    }

    public function testGettingTheTailOfAClassName(): void
    {
        $this->expectException(InvalidName::class);

        QualifiedName::fromString('TestClass')->tail();
    }

    protected function createFromArray(array $parts): QualifiedName
    {
        return QualifiedName::fromArray($parts);
    }

    protected function createFromString(string $string): QualifiedName
    {
        return QualifiedName::fromString($string);
    }
}
