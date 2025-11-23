<?php

namespace Phpactor\Name\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Phpactor\Name\Exception\InvalidName;
use Phpactor\Name\Name;
use Phpactor\Name\QualifiedName;

abstract class AbstractQualifiedNameTestCase extends TestCase
{
    #[DataProvider('provideCreateFromArray')]
    public function testCreateFromArray(array $parts, string $expected): void
    {
        $this->assertEquals($expected, $this->createFromArray($parts));
    }

    public static function provideCreateFromArray()
    {
        yield [
            ['Hello'],
            'Hello'
        ];

        yield [
            ['Hello', 'Goodbye'],
            'Hello\\Goodbye'
        ];
    }

    #[DataProvider('provideCreateFromString')]
    public function testCreateFromString(string $string, string $expected): void
    {
        $this->assertEquals($expected, $this->createFromString($string));
    }

    public static function provideCreateFromString()
    {
        yield [
            '\\Hello',
            'Hello'
        ];

        yield [
            'Hello\\',
            'Hello'
        ];

        yield [
            'Hello',
            'Hello'
        ];

        yield [
            'Hello\\Goodbye',
            'Hello\\Goodbye'
        ];
    }

    public function testThrowsExceptionIfNameIsEmpty(): void
    {
        $this->expectException(InvalidName::class);
        QualifiedName::fromString('');
    }

    public function testHead(): void
    {
        $original = $this->createFromArray([
            'Foobar',
            'Barfoo'
        ]);
        $this->assertEquals(
            'Barfoo',
            $original->head()->__toString()
        );
        ;
        $this->assertEquals('Foobar\\Barfoo', $original->__toString());
    }

    public function testTail(): void
    {
        $original = $this->createFromArray([
            'Foobar',
            'Barbar',
            'Barfoo'
        ]);
        $this->assertEquals(
            'Foobar\\Barbar',
            $original->tail()->__toString()
        );
        ;
        $this->assertEquals('Foobar\\Barbar\\Barfoo', $original->__toString());
    }

    public function testIsDescendantOf(): void
    {
        $one = $this->createFromString('One\\Two');
        $this->assertTrue(
            $this->createFromString('One\\Two\\Three')->isDescendantOf($one)
        );
        $this->assertFalse(
            $this->createFromString('One\\Four\\Three')->isDescendantOf($one)
        );
    }

    public function testIsCountable(): void
    {
        $this->assertCount(3, $this->createFromArray(['1', '2', '3']));
        $this->assertCount(1, $this->createFromArray(['1']));
    }

    public function testPrepend(): void
    {
        $one = $this->createFromString('Three\\Four');
        $two = $this->createFromString('One\\Two');
        $this->assertEquals('One\\Two\\Three\\Four', $one->prepend($two)->__toString());
    }

    public function testAppend(): void
    {
        $one = $this->createFromString('Three\\Four');
        $two = $this->createFromString('One\\Two');
        $this->assertEquals('One\\Two\\Three\\Four', $two->append($one)->__toString());
    }

    public function testToArray(): void
    {
        $this->assertEquals(
            ['One', 'Two'],
            $this->createFromString('One\\Two')->toArray()
        );
    }

    /**
     * @return Name
     */
    protected function createFromArray(array $parts)
    {
        return QualifiedName::fromArray($parts);
    }

    /**
     * @return Name
     */
    protected function createFromString(string $string)
    {
        return QualifiedName::fromString($string);
    }
}
