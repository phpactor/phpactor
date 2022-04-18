<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use Generator;
use Phpactor\WorseReflection\Core\Inference\Assignments;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\LocalAssignments;
use Phpactor\WorseReflection\Core\Inference\PropertyAssignments;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Problems;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\TypeFactory;

class FrameTest extends TestCase
{
    /**
     * @testdox It returns local and class assignments.
     */
    public function testAssignments(): void
    {
        $frame = new Frame('test');
        $this->assertInstanceOf(LocalAssignments::class, $frame->locals());
        $this->assertInstanceOf(PropertyAssignments::class, $frame->properties());
    }

    public function testReduce(): void
    {
        $s1 = NodeContext::none();
        $s2 = NodeContext::none();

        $frame = new Frame('test');
        $frame->problems()->add($s1);

        $child = $frame->new('child');
        $child->problems()->add($s2);

        $problems = $frame->reduce(function (Frame $frame, Problems $problems) {
            return $problems->merge($frame->problems());
        }, Problems::create());

        $this->assertEquals([ $s1, $s2 ], $problems->toArray());
    }

    /**
     * @dataProvider provideResetToStateBefore
     */
    public function testResetToStateBefore(Frame $frame, int $before, int $after, Frame $expected): void
    {
        $frame->restoreToStateBefore($before, $after);
        self::assertEquals($expected, $frame);
    }

    public function provideResetToStateBefore(): Generator
    {
        yield [
            new Frame('foo', new LocalAssignments([
                new Variable('this', 10, TypeFactory::int()),
                new Variable('this', 20, TypeFactory::string()),
            ])),
            20,
            30,
            new Frame('foo', new LocalAssignments([
                new Variable('this', 10, TypeFactory::int()),
                new Variable('this', 20, TypeFactory::string()),
                new Variable('this', 30, TypeFactory::int()),
            ])),
        ];

        yield 'does not restore if var not modified' => [
            (function () {
                $frame =  new Frame('foo', new LocalAssignments([
                    new Variable('this', 10, TypeFactory::int()),
                    new Variable('foo', 20, TypeFactory::string()),
                ]));
                $frame->restoreToStateBefore(20, 30);

                return $frame;
            })(),
            20,
            30,
            new Frame('foo', new LocalAssignments([
                new Variable('this', 10, TypeFactory::int()),
                new Variable('foo', 20, TypeFactory::string()),
                new Variable('foo', 30, TypeFactory::undefined()),
            ])),
        ];
    }
}
