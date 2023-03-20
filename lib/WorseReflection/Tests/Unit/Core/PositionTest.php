<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\ByteOffsetRange;

class PositionTest extends TestCase
{
    /**
     * @testdox It provides width
     */
    public function testWidth(): void
    {
        $position = ByteOffsetRange::fromInts(15, 35);
        $this->assertEquals(15, $position->start()->asInt());
        $this->assertEquals(35, $position->endAsInt());
    }
}
