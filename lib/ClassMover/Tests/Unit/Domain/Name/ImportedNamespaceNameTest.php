<?php

namespace Phpactor\ClassMover\Tests\Unit\Domain\Name;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Domain\Name\ImportedName;
use InvalidArgumentException;

class ImportedNamespaceNameTest extends TestCase
{
    #[TestDox('It show replace the head.')]
    public function testWithAlias(): void
    {
        $imported = ImportedName::fromString('Foobar\\Barfoo\\FooFoo');
        $imported = $imported->withAlias('BarBar');
        $this->assertEquals('Foobar\\Barfoo\\FooFoo', $imported->__toString());
    }

    #[TestDox('It allows single part namespace.')]
    public function testSinglePart(): void
    {
        $imported = ImportedName::fromString('Foobar');
        $this->assertEquals('Foobar', $imported->__toString());
    }

    #[TestDox('It does not allow empty namespace.')]
    public function testEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Name cannot be empty');
        ImportedName::fromString('');
    }
}
