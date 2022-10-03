<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use Phpactor\CodeBuilder\Domain\Prototype\Classes;
use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use PHPUnit\Framework\TestCase;

class ClassesTest extends TestCase
{
    /**
     * @testdox Create from classes
     */
    public function testCreateFromClasses(): void
    {
        $classes = Classes::fromClasses([
            new ClassPrototype('One'),
            new ClassPrototype('Two'),
        ]);
        $this->assertCount(2, iterator_to_array($classes));
    }
}
