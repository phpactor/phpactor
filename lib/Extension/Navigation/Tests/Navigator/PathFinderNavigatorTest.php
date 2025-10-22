<?php

declare(strict_types=1);

namespace Phpactor\Extension\Navigation\Tests\Navigator;

use Phpactor\Extension\Navigation\Navigator\PathFinderNavigator;
use Phpactor\PathFinder\PathFinder;
use PHPUnit\Framework\TestCase;

class PathFinderNavigatorTest extends TestCase
{
    private PathFinder $pathFinder;

    public function setUp(): void
    {
        $this->pathFinder = PathFinder::fromDestinations([
        'source' => 'src/<kernel>.php',
        'unit_test' => 'tests/Unit/<kernel>Test.php'
        ]);
    }

    public function testSomething(): void
    {
        $navigator = new PathFinderNavigator($this->pathFinder);
        $result = $navigator->destinationsFor('src/Kernel.php');

        self::assertSame(['unit_test' => 'tests/Unit/KernelTest.php'], $result);
    }

}
