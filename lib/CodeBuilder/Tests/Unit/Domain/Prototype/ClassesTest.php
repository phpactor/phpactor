<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\Classes;

class ClassesTest extends TestCase
{
    #[TestDox('Create from classes')]
    public function testCreateFromClasses(): void
    {
        $classes = Classes::fromClasses([
            new ClassPrototype('One'),
            new ClassPrototype('Two'),
        ]);
        $this->assertCount(2, iterator_to_array($classes));
    }
}
