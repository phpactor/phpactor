<?php

namespace Phpactor\ClassMover\Tests\Unit\Domain\Name;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Domain\Name\QualifiedName;

class QualifiedNameTest extends TestCase
{
    /**
     * It can say if it is equal to another namespace.
     */
    public function testEqualTo(): void
    {
        $name = QualifiedName::fromString('Foo\\Bar');
        $notMatching = QualifiedName::fromString('Bar\\Bar');
        $matching = QualifiedName::fromString('Foo\\Bar');

        $this->assertFalse($name->isEqualTo($notMatching));
        $this->assertTrue($name->isEqualTo($matching));
    }
}
