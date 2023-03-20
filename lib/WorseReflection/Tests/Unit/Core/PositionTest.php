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
        $position = Position::fromInts(15, 35);
        $this->assertEquals(15, $position->startAsInt());
        $this->assertEquals(35, $position->endAsInt());
    }
}
