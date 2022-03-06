<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Integration\Model;

use Composer\Package\CompletePackageInterface;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionFileGenerator;
use Phpactor\Extension\ExtensionManager\Model\Extensions;
use Phpactor\Extension\ExtensionManager\Tests\Integration\IntegrationTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ExtensionFileGeneratorTest extends IntegrationTestCase
{
    const EXAMPLE_CLASS_NAME_1 = 'Foo\\Bar';
    const EXAMPLE_CLASS_NAME_2 = 'Foo\\Baz';

    /**
     * @var ObjectProphecy
     */
    private $package;

    /**
     * @var ExtensionFileGenerator
     */
    private $generator;

    /**
     * @var string
     */
    private $path;

    /**
     * @var ObjectProphecy
     */
    private $extension1;

    /**
     * @var ObjectProphecy
     */
    private $extension2;

    public function setUp(): void
    {
        parent::setUp();

        $this->package = $this->prophesize(CompletePackageInterface::class);
        $this->path = $this->workspace->path('extensions.php');
        $this->generator = new ExtensionFileGenerator($this->path);

        $this->extension1 = $this->prophesize(Extension::class);
        $this->extension1->name()->willReturn('test_extension_1');
        $this->extension2 = $this->prophesize(Extension::class);
        $this->extension2->name()->willReturn('test_extension_2');
    }

    public function testGenerate(): void
    {
        $this->extension1->classNames()->willReturn([self::EXAMPLE_CLASS_NAME_1]);
        $this->extension2->classNames()->willReturn([self::EXAMPLE_CLASS_NAME_2]);
        $this->generator->writeExtensionList(new Extensions([
            $this->extension1->reveal(),
            $this->extension2->reveal(),
        ]));

        $extensions = require($this->path);
        $this->assertEquals([
            '\\' . self::EXAMPLE_CLASS_NAME_1,
            '\\' . self::EXAMPLE_CLASS_NAME_2,
        ], $extensions);
    }

    public function testGeneratesNonExistingDirectory(): void
    {
        $path = $this->workspace->path('Foo/Foobar/Bar/extensions.php');
        $generator = new ExtensionFileGenerator($path);
        $generator->writeExtensionList(new Extensions([]));
        $this->assertFileExists($path);
    }
}
