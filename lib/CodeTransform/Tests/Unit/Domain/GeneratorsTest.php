<?php

namespace Phpactor\CodeTransform\Tests\Unit\Domain;

use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeTransform\Domain\Generator;
use Phpactor\CodeTransform\Domain\Generators;

class GeneratorsTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @testdox It can retrieve and iterate generators.
     */
    public function testIterateAndRetrieve(): void
    {
        $generator1 = $this->prophesize(Generator::class);
        $generator2 = $this->prophesize(Generator::class);

        $generators = Generators::fromArray([
            'one' => $generator1->reveal(),
            'two' => $generator2->reveal(),
        ]);

        $this->assertSame($generator1->reveal(), $generators->get('one'));
        $this->assertCount(2, $generators);
        $this->assertSame([
            'one' => $generator1->reveal(),
            'two' => $generator2->reveal(),
        ], iterator_to_array($generators));
    }
}
