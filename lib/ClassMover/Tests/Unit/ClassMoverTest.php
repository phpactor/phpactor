<?php

namespace Phpactor\ClassMover\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassMover\Domain\ClassFinder;
use Prophecy\Prophecy\ObjectProphecy;

class ClassMoverTest extends TestCase
{
    public function setUp(): void
    {
        $this->finder = $this->prophesize(ClassFinder::class);
    }
}
