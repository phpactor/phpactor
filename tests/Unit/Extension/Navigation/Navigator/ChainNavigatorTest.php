<?php

namespace Phpactor\Tests\Unit\Extension\Navigation\Navigator;

use Phpactor\Extension\Navigation\Navigator\ChainNavigator;
use Phpactor\Extension\Navigation\Navigator\Navigator;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ChainNavigatorTest extends TestCase
{
    use ProphecyTrait;
    public const TEST_PATH = '/path/to/test.php';
    public const TEST_DESTINATION_1 = '/destination1.php';
    public const TEST_DESTINATION_2 = '/destination2.php';

    private ObjectProphecy $navigator1;

    private ObjectProphecy $navigator2;

    public function setUp(): void
    {
        $this->navigator1 = $this->prophesize(Navigator::class);
        $this->navigator2 = $this->prophesize(Navigator::class);
    }

    public function testReturnsEmptyArrayWhenNoNavigators(): void
    {
        $navigator = $this->create([]);
        $destinations = $navigator->destinationsFor(self::TEST_PATH);
        $this->assertEquals([], $destinations);
    }

    public function testMergesResultsOfTwoNavigators(): void
    {
        $navigator = $this->create([
            $this->navigator1->reveal(),
            $this->navigator2->reveal(),
        ]);

        $this->navigator1->destinationsFor(self::TEST_PATH)->willReturn([ 'dest1' => self::TEST_DESTINATION_1 ]);
        $this->navigator2->destinationsFor(self::TEST_PATH)->willReturn([ 'dest2' => self::TEST_DESTINATION_2 ]);

        $destinations = $navigator->destinationsFor(self::TEST_PATH);

        $this->assertEquals([
            'dest1' => self::TEST_DESTINATION_1,
            'dest2' => self::TEST_DESTINATION_2,
        ], $destinations);
    }

    private function create(array $navigators): Navigator
    {
        return new ChainNavigator($navigators);
    }
}
