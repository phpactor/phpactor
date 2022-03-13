<?php

namespace Phpactor\Tests\Unit\Extension\Navigation\Navigator;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Navigation\Navigator\PathFinderNavigator;
use Phpactor\PathFinder\PathFinder;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class PathFinderNavigatorTest extends TestCase
{
    use ProphecyTrait;
    const TEST_PATH = '/test/path';

    private ObjectProphecy $pathFinder;

    private PathFinderNavigator $navigator;

    public function setUp(): void
    {
        $this->pathFinder = $this->prophesize(PathFinder::class);
        $this->navigator = new PathFinderNavigator($this->pathFinder->reveal());
    }

    public function testDelegatesToPathFinder(): void
    {
        $destinations = ['one' => 'two'];
        $this->pathFinder->destinationsFor(self::TEST_PATH)->willReturn($destinations);
        $result = $this->navigator->destinationsFor(self::TEST_PATH);

        $this->assertEquals($destinations, $result);
    }
}
