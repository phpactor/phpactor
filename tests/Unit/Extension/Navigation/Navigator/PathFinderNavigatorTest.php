<?php

namespace Phpactor\Tests\Unit\Extension\Navigation\Navigator;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\Extension\Navigation\Navigator\PathFinderNavigator;

class PathFinderNavigatorTest extends TestCase
{
    const TEST_PATH = '/test/path';

    /**
     * @var ObjectProphecy
     */
    private $pathFinder;

    /**
     * @var PathFinderNavigator
     */
    private $navigator;

    public function setUp()
    {
        $this->pathFinder = $this->prophesize(PathFinder::class);
        $this->navigator = new PathFinderNavigator($this->pathFinder->reveal());
    }

    public function testDelegatesToPathFinder()
    {
        $destinations = ['one' => 'two'];
        $this->pathFinder->destinationsFor(self::TEST_PATH)->willReturn($destinations);
        $result = $this->navigator->destinationsFor(self::TEST_PATH);

        $this->assertEquals($destinations, $result);
    }
}
