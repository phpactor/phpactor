<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Adapter\Composer;

use Composer\Package\CompletePackage;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\Exception\InvalidExtensionPackage;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\PackageExtensionFactory;

class PackageExtensionFactoryTest extends TestCase
{
    const EXAMPLE_EXTENSION_NAME = 'foobar';
    const EXAMPLE_VERSION = 'dev-master';
    const EXAMPLE_EXTENSION_CLASS = 'Example\\\\ExampleExtension';


    /**
     * @var PackageExtensionFactory
     */
    private $factory;

    public function setUp(): void
    {
        $this->factory = new PackageExtensionFactory();
    }

    public function testFromPackage(): void
    {
        $package = $this->createExamplePackage();
        $package->setType(PackageExtensionFactory::PACKAGE_TYPE);
        $package->setExtra([
            PackageExtensionFactory::EXTRA_EXTENSION_CLASS => self::EXAMPLE_EXTENSION_CLASS,
        ]);

        $extension = $this->factory->fromPackage($package);

        $this->assertEquals(self::EXAMPLE_EXTENSION_NAME, $extension->name());
        $this->assertEquals(self::EXAMPLE_VERSION, $extension->version());
        $this->assertEquals([self::EXAMPLE_EXTENSION_CLASS], $extension->classNames());
    }

    public function testExceptionOnInvalidPackageType(): void
    {
        $this->expectException(InvalidExtensionPackage::class);
        $this->expectExceptionMessage('Package "foobar" has type "foo"');

        $package = $this->createExamplePackage();
        $package->setType('foo');

        $this->factory->fromPackage($package);
    }

    public function testExceptionIfPackageIsMissingTheClassExtraKey(): void
    {
        $this->expectException(InvalidExtensionPackage::class);
        $this->expectExceptionMessage('Package "foobar" does not have');

        $package = $this->createExamplePackage();
        $package->setType(PackageExtensionFactory::PACKAGE_TYPE);

        $this->factory->fromPackage($package);
    }

    private function createExamplePackage(): CompletePackage
    {
        $package = new CompletePackage(
            self::EXAMPLE_EXTENSION_NAME,
            self::EXAMPLE_VERSION,
            self::EXAMPLE_VERSION
        );
        return $package;
    }
}
