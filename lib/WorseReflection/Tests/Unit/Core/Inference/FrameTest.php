<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use Phpactor\WorseReflection\Core\Inference\Assignments;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\ConcreteFrame;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Inference\PropertyAssignments;

class FrameTest extends TestCase
{
    /**
     * @testdox It returns local and class assignments.
     */
    public function testAssignments(): void
    {
        $frame = new ConcreteFrame();
        $this->assertInstanceOf(LocalAssignments::class, $frame->locals());
        $this->assertInstanceOf(PropertyAssignments::class, $frame->properties());
    }
}
