<?php

namespace Phpactor\Tests\Unit\Extension\Navigation\Navigator;

use PHPUnit\Framework\TestCase;
use Phpactor\ClassFileConverter\PathFinder;
use Phpactor\Extension\Navigation\Navigator\PathFinderNavigator;
use Prophecy\PhpUnit\ProphecyTrait;

class PathFinderNavigatorTest extends TestCase
{
    use ProphecyTrait;

    const TEST_PATH = '/test/path';

    /**
     * @var ObjectProphecy
     */
    private $pathFinder;

    /**
     * @var PathFinderNavigator
     */
    private $navigator;

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
