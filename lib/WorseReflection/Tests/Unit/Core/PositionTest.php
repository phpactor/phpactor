<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Position;

class PositionTest extends TestCase
{
    /**
     * @testdox It provides width
     */
    public function testWidth(): void
    {
        $position = Position::fromStartAndEnd(15, 35);
        $this->assertEquals(15, $position->start());
        $this->assertEquals(35, $position->end());
        $this->assertEquals(20, $position->width());
    }
}
