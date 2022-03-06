<?php

namespace Phpactor\ClassMover\Tests\Unit\Domain\Name;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Domain\Name\MemberName;

class MemberNameTest extends TestCase
{
    public function testValidName(): void
    {
        $name = MemberName::fromString('foobar');
        $this->assertEquals('foobar', (string) $name);
    }

    public function testCompareDollars(): void
    {
        $name = MemberName::fromString('$foobar');
        $this->assertTrue($name->matches('foobar'));
        $this->assertTrue($name->matches('$foobar'));

        $name = MemberName::fromString('foobar');
        $this->assertTrue($name->matches('$foobar'));

        $name = MemberName::fromString('foobar');
        $this->assertTrue($name->matches('foobar'));
    }
}
