<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\NameImports;
use Phpactor\WorseReflection\Core\Name;
use RuntimeException;

class NameImportsTest extends TestCase
{
    public function testByAlias(): void
    {
        $imports = NameImports::fromNames([
            'Barfoo' => Name::fromString('Foobar\\Barfoo'),
        ]);

        $this->assertTrue($imports->hasAlias('Barfoo'));
        $this->assertEquals(
            Name::fromString('Foobar\\Barfoo'),
            $imports->getByAlias('Barfoo')
        );
    }

    public function testResolveAliasedLocalName(): void
    {
        $imports = NameImports::fromNames([
            'Baz' => Name::fromString('Foobar\\Barfoo'),
        ]);

        $this->assertEquals(
            Name::fromString('Baz'),
            $imports->resolveLocalName(Name::fromString('Foobar\\Barfoo'))
        );
    }

    public function testResolveRelativeAliasedLocalName(): void
    {
        $imports = NameImports::fromNames([
            'Baz' => Name::fromString('Foobar\\Barfoo'),
        ]);

        $this->assertEquals(
            Name::fromString('Baz\\Zoz'),
            $imports->resolveLocalName(
                Name::fromString('Foobar\\Barfoo\\Zoz')
            )
        );
    }

    public function testResolveRelativeAliasedLocalName2(): void
    {
        $imports = NameImports::fromNames([
            'Baz' => Name::fromString('Foobar\\Barfoo'),
        ]);

        $this->assertEquals(
            Name::fromString('Baz\\Zoz\\Foo'),
            $imports->resolveLocalName(
                Name::fromString('Foobar\\Barfoo\\Zoz\\Foo')
            )
        );
    }

    public function testLocalNameIfNoImport(): void
    {
        $imports = NameImports::fromNames([
        ]);

        $this->assertEquals(
            Name::fromString('Foo'),
            $imports->resolveLocalName(
                Name::fromString('Foobar\\Barfoo\\Zoz\\Foo')
            )
        );
    }

    public function testAliasNotFound(): void
    {
        $this->expectException(RuntimeException::class);

        $imports = NameImports::fromNames([]);

        $imports->getByAlias('Barfoo');
    }
}
