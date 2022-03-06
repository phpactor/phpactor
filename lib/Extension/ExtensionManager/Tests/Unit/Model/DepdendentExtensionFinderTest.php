<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Model\DependentExtensionFinder;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionRepository;
use Phpactor\Extension\ExtensionManager\Model\Extensions;

class DepdendentExtensionFinderTest extends TestCase
{
    const EXAMPLE_PACKAGE = 'example-package';

    /**
     * @var ObjectProphecy|ExtensionRepository
     */
    private $repository;

    /**
     * @var DepdendentPackageFinder
     */
    private $finder;

    /**
     * @var ObjectProphecy
     */
    private $extension;

    public function setUp(): void
    {
        $this->repository = $this->prophesize(ExtensionRepository::class);
        $this->finder = new DependentExtensionFinder($this->repository->reveal());
        $this->extension = $this->prophesize(Extension::class);
        $this->extension->name()->willReturn(self::EXAMPLE_PACKAGE);
    }

    public function testReturnsEmptyRepositoryHasNoExtensions(): void
    {
        $this->repository->find(self::EXAMPLE_PACKAGE)->willReturn(
            $this->extension->reveal()
        );
        $this->repository->installedExtensions()->willReturn(new Extensions([]));

        $dependants = $this->finder->findDependentExtensions([ self::EXAMPLE_PACKAGE ]);

        $this->assertEmpty($dependants);
    }

    public function testReturnsEmptyWhenNoDependentsDepend(): void
    {
        $this->repository->find(self::EXAMPLE_PACKAGE)->willReturn(
            $this->extension->reveal()
        );
        $this->repository->installedExtensions()->willReturn(new Extensions([
            $this->createExtension('foo', [ 'bar', 'foo' ]),
            $this->createExtension('zed', [ 'bar', 'foo' ]),
        ]));

        $dependants = $this->finder->findDependentExtensions([ self::EXAMPLE_PACKAGE ]);

        $this->assertEmpty($dependants);
    }

    public function testReturnsDependentPackages(): void
    {
        $this->repository->find(self::EXAMPLE_PACKAGE)->willReturn(
            $this->extension->reveal()
        );
        $this->repository->installedExtensions()->willReturn(new Extensions([
            $this->createExtension('foo', [ 'bar', 'foo' ]),
            $this->createExtension('zed', [ 'bar', self::EXAMPLE_PACKAGE ]),
            $this->createExtension('zog', [ self::EXAMPLE_PACKAGE, 'foo' ]),
        ]));

        $dependants = $this->finder->findDependentExtensions([ self::EXAMPLE_PACKAGE ]);

        $this->assertCount(2, $dependants);
        $dependants = iterator_to_array($dependants);
        $this->assertEquals('zed', $dependants['zed']->name());
        $this->assertEquals('zog', $dependants['zog']->name());
    }

    public function testReturnsDependenciesOfTheDependency(): void
    {
        $this->repository->find(self::EXAMPLE_PACKAGE)->willReturn(
            $this->extension->reveal()
        );
        $this->repository->installedExtensions()->willReturn(new Extensions([
            $this->createExtension('foo', [ 'bar', 'foo' ]),
            $this->createExtension('zed', [ 'bar', self::EXAMPLE_PACKAGE ]),
            $this->createExtension('zog', [ 'zed' ]),
        ]));

        $dependants = $this->finder->findDependentExtensions([ self::EXAMPLE_PACKAGE ]);

        $this->assertCount(2, $dependants);
    }

    public function testWillNotSufferCircularDependencies(): void
    {
        $this->repository->find(self::EXAMPLE_PACKAGE)->willReturn(
            $this->extension->reveal()
        );
        $this->repository->installedExtensions()->willReturn(new Extensions([
            $this->createExtension('foo', [ 'bar', self::EXAMPLE_PACKAGE ]),
            $this->createExtension('bar', [ 'foo' ]),
        ]));

        $dependants = $this->finder->findDependentExtensions([ self::EXAMPLE_PACKAGE ]);

        $this->assertCount(2, $dependants);
    }

    private function createExtension(string $string, array $array)
    {
        $extension = $this->prophesize(Extension::class);
        $extension->name()->willReturn($string);
        $extension->dependencies()->willReturn($array);

        return $extension->reveal();
    }
}
