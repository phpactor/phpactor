<?php

namespace Phpactor\Tests\Unit\Composer;

use Composer\Autoload\ClassLoader;
use Phpactor\Composer\ClassNameResolver;
use Phpactor\Composer\ClassFqn;

class ClassNameResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var mixed
     */
    private $classLoader;
    
    /**
     * @var ClassNameResolver
     */
    private $classNameResolver;

    public function setUp()
    {
        $this->classLoader = $this->prophesize(ClassLoader::class);
        $this->classNameResolver = new ClassNameResolver($this->classLoader->reveal());
    }

    /**
     * @dataProvider provideResolveClassNameFromFile
     */
    public function testResolveClassNameFromFile(array $prefixes = [], string $path, string $expectedClassName)
    {
        $prefixes = array_merge([
            'psr-0' => [],
            'psr-4' => [],
            'classmap' => [],
        ], $prefixes);

        $this->classLoader->getPrefixes()->willReturn($prefixes['psr-0']);
        $this->classLoader->getPrefixesPsr4()->willReturn($prefixes['psr-4']);
        $this->classLoader->getClassMap()->willReturn($prefixes['classmap']);

        $classFqn = $this->classNameResolver->resolve($path);

        $this->assertEquals(ClassFqn::fromString($expectedClassName), $classFqn);
    }

    public function provideResolveClassNameFromFile()
    {
        return [
            [
                [
                    'psr-0' => [
                        'Namespace\\Foobar\\' => 'tests',
                    ],
                ],
                'tests/Unit/Composer/ClassNameResolverTest.php',
                'Namespace\\Foobar\\Unit\\Composer\\ClassNameResolverTest',
            ],
            [
                [
                    'psr-0' => [
                        'Namespace\\Foobar' => 'tests',
                    ],
                ],
                'tests/Unit/Composer/ClassNameResolverTest.php',
                'Namespace\\Foobar\\Unit\\Composer\\ClassNameResolverTest',
            ],
            [
                [
                    'psr-0' => [
                        'Namespace\\Foobar' => 'tests/',
                    ],
                ],
                'tests/Unit/Composer/ClassNameResolverTest.php',
                'Namespace\\Foobar\\Unit\\Composer\\ClassNameResolverTest',
            ],
            [
                [
                    'psr-4' => [
                        'Namespace\\Foobar' => 'tests/',
                    ],
                ],
                'tests/Unit/Composer/ClassNameResolverTest.php',
                'Namespace\\Foobar\\Unit\\Composer\\ClassNameResolverTest',
            ],
            [
                [
                    'classmap' => [
                        'Namespace\\Foobar\\Unit\\Composer\\ClassNameResolverTest' => 'tests/Unit/Composer/ClassNameResolverTest.php',
                    ],
                ],
                'tests/Unit/Composer/ClassNameResolverTest.php',
                'Namespace\\Foobar\\Unit\\Composer\\ClassNameResolverTest',
            ],
        ];
    }
}
