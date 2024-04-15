<?php

namespace Phpactor\PathFinder\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\PathFinder\Exception\NoPlaceHoldersException;
use Phpactor\PathFinder\PathFinder;
use Phpactor\PathFinder\Exception\NoMatchingSourceException;

class PathFinderTest extends TestCase
{
    const PROJECT_ROOT = '/home/user/src/github.com/organisation/project';

    /**
     * @dataProvider provideTeleport
     */
    public function testTeleport(array $targets, string $path, array $expectedTargets): void
    {
        $teleport = PathFinder::fromAbsoluteDestinations(self::PROJECT_ROOT, $targets);
        $targets = $teleport->destinationsFor($path);

        $this->assertEquals($expectedTargets, $targets);
    }

    /**
     * @return Generator<string,array{array<string,string>,string,array<string,string>}>
     */
    public function provideTeleport(): Generator
    {
        yield 'no available targets' => [
            [
                'target1' => 'lib/<kernel>.php',
            ],
            'lib/MyFile.php',
            [
            ],
        ];

        yield 'one available target' => [
            [
                'target1' => 'lib/<kernel>.php',
                'target2' => 'tests/<kernel>Test.php',
            ],
            'lib/MyFile.php',
            [
                'target2' => 'tests/MyFileTest.php',
            ],
        ];

        yield 'multiple matching targets' => [
            [
                'target1' => 'lib/<kernel>.php',
                'target2' => 'tests/<kernel>Test.php',
                'target3' => 'benchmarks/<kernel>Bench.php',
            ],
            'lib/MyFile.php',
            [
                'target2' => 'tests/MyFileTest.php',
                'target3' => 'benchmarks/MyFileBench.php',
            ],
        ];

        yield 'composite path' => [
            [
                'target1' => 'lib/<kernel>.php',
                'target2' => 'tests/<kernel>Test.php',
            ],
            'lib/Foobar/Barfoo/MyFile.php',
            [
                'target2' => 'tests/Foobar/Barfoo/MyFileTest.php',
            ],
        ];

        yield 'absolute path' => [
            [
                'target1' => 'lib/<kernel>.php',
                'target2' => 'tests/<kernel>Test.php',
            ],
            '/home/daniel/lib/Foobar/Barfoo/MyFile.php',
            [
                'target2' => 'tests/Foobar/Barfoo/MyFileTest.php',
            ],
        ];

        yield 'relative path' => [
            [
                'target1' => 'lib/<kernel>.php',
                'target2' => 'tests/<kernel>Test.php',
            ],
            '/home/daniel/lib/Foobar/../Foobar/Barfoo/MyFile.php',
            [
                'target2' => 'tests/Foobar/Barfoo/MyFileTest.php',
            ],
        ];

        yield 'from unit test' => [
            [
                'target1' => 'lib/<kernel>.php',
                'target2' => 'tests/Unit/<kernel>Test.php',
            ],
            'tests/Unit/MyFileTest.php',
            [
                'target1' => 'lib/MyFile.php',
            ],
        ];

        yield 'multiple segments 1' => [
            [
                'target1' => 'lib/<module>/<kernel>.php',
                'target2' => 'tests/<module>/Unit/<kernel>Test.php',
            ],
            'tests/ModuleOne/Unit/MyFileTest.php',
            [
                'target1' => 'lib/ModuleOne/MyFile.php',
            ],
        ];

        yield 'multiple segments 2' => [
            [
                'target1' => 'lib/<module>/<kernel>.php',
                'target2' => 'tests/<module>/Unit/<kernel>Test.php',
            ],
            'lib/ModuleOne/MyFile.php',
            [
                'target2' => 'tests/ModuleOne/Unit/MyFileTest.php',
            ],
        ];

        yield 'multiple segments with containing multiple elements' => [
            [
                'target1' => 'lib/<module>/<kernel>.php',
                'target2' => 'tests/<module>/Unit/<kernel>Test.php',
            ],
            'lib/ModuleOne/Model/Abstractor/MyFile.php',
            [
                'target2' => 'tests/ModuleOne/Unit/Model/Abstractor/MyFileTest.php',
            ],
        ];

        yield 'mixed multiple segments' => [
            [
                'target1' => 'lib/<module>/<kernel>.php',
                'target2' => 'tests/<kernel>/Unit/<module>Test.php',
            ],
            'lib/ModuleOne/Model/Abstractor/MyFile.php',
            [
                'target2' => 'tests/Model/Abstractor/MyFile/Unit/ModuleOneTest.php',
            ],
        ];

        yield 'multiple with missing placeholders' => [
            [
                'target1' => 'lib/<module>/<kernel>.php',
                'target2' => 'tests/Unit/<kernel>Test.php',
            ],
            'lib/ModuleOne/Model/Abstractor/MyFile.php',
            [
                'target2' => 'tests/Unit/Model/Abstractor/MyFileTest.php',
            ],
        ];

        yield 'multiple with non-correlating placeholders' => [
            [
                'target1' => 'lib/<foo>/<bar>.php',
                'target2' => 'tests/Unit/<kernel>Test.php',
            ],
            'lib/ModuleOne/Model/Abstractor/MyFile.php',
            [
                'target2' => 'tests/Unit/Test.php',
            ],
        ];

        yield 'one available target with directories with identical name' => [
            [
                'target1' => 'src/<kernel>.php',
                'target2' => 'tests/<kernel>Test.php',
            ],
            self::PROJECT_ROOT . '/src/MyFile.php',
            [
                'target2' => 'tests/MyFileTest.php',
            ],
        ];

        yield 'jump back to one available target with directories with identical name' => [
            [
                'target1' => 'src/<kernel>.php',
                'target2' => 'tests/<kernel>Test.php',
            ],
            self::PROJECT_ROOT . '/tests/MyFileTest.php',
            [
                'target1' => 'src/MyFile.php',
            ],
        ];
    }

    public function testNoMatchingTarget(): void
    {
        $this->expectException(NoMatchingSourceException::class);
        $this->expectExceptionMessage('Could not find matching source pattern for "/lib/Foo.php", known patterns: "/soos/<kernel>/boos.php"');

        $teleport = PathFinder::fromDestinations([
            'soos' => '/soos/<kernel>/boos.php',
        ]);

        $teleport->destinationsFor('/lib/Foo.php');
    }

    public function testDestinationWithNoKernel(): void
    {
        $this->expectException(NoPlaceHoldersException::class);
        $this->expectExceptionMessage('File pattern "/soos/boos.php" does not contain any <placeholders>');

        $teleport = PathFinder::fromDestinations([
            'soos' => '/soos/boos.php',
        ]);

        $teleport->destinationsFor('/lib/Foo.php');
    }
}
