<?php

namespace Phpactor\CodeTransform\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\ClassName;

class ClassNameTest extends TestCase
{
    /**
     * It returns the namespace
     */
    public function testNamespace(): void
    {
        $class = ClassName::fromString('This\\Is\\A\\Namespace\\ClassName');
        $this->assertEquals('This\\Is\\A\\Namespace', $class->namespace());
    }

    /**
     * It returns empty strsing if no namespace
     */
    public function testNamespaceNone(): void
    {
        $class = ClassName::fromString('ClassName');
        $this->assertEquals('', $class->namespace());
    }

    #[TestDox('It returns the class short name')]
    public function testShort(): void
    {
        $class = ClassName::fromString('Namespace\\ClassName');
        $this->assertEquals('ClassName', $class->short());
    }

    #[TestDox('It returns the class short name with no namespace')]
    public function testShortNoNamespace(): void
    {
        $class = ClassName::fromString('ClassName');
        $this->assertEquals('ClassName', $class->short());
    }

    #[TestDox('It throws exception if classname is empty.')]
    public function testEmpty(): void
    {
        $this->expectExceptionMessage('Class name cannot be empty');
        ClassName::fromString('');
    }
}
