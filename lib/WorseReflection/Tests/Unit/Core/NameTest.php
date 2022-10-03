<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use Phpactor\WorseReflection\Core\Name;
use PHPUnit\Framework\TestCase;

class NameTest extends TestCase
{
    public function testHead(): void
    {
        $name = Name::fromString('Foo\\Bar\\Baz');
        $this->assertEquals('Foo', (string) $name->head());
    }

    public function testTail(): void
    {
        $name = Name::fromString('Foo\\Bar\\Baz');
        $this->assertEquals('Bar\\Baz', (string) $name->tail());
    }

    public function testIsFullyQualified(): void
    {
        $name = Name::fromString('\\Foo\\Bar\\Baz');
        $this->assertTrue($name->wasFullyQualified());
    }
}
