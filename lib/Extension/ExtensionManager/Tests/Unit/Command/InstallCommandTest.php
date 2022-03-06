<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Command\InstallCommand;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Symfony\Component\Console\Tester\CommandTester;

class InstallCommandTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $installer;

    /**
     * @var CommandTester
     */
    private $tester;


    public function setUp(): void
    {
        $this->installer = $this->prophesize(InstallerService::class);
        $this->tester = new CommandTester(new InstallCommand($this->installer->reveal()));
    }

    public function testItCallsTheInstaller(): void
    {
        $this->installer->install()->shouldBeCalled();

        $this->tester->execute([]);
        $this->assertEquals(0, $this->tester->getStatusCode());
    }

    public function testItInstallsASingleExtension(): void
    {
        $this->installer->requireExtensions(['foobar'])->shouldBeCalled();

        $this->tester->execute([
            'extension' => [ 'foobar' ]
        ]);


        $this->assertEquals(0, $this->tester->getStatusCode());
    }

    public function testItInstallsManyExtensions(): void
    {
        $this->installer->requireExtensions(['foobar', 'barfoo'])->shouldBeCalled();

        $this->tester->execute([
            'extension' => [ 'foobar', 'barfoo' ]
        ]);

        $this->assertEquals(0, $this->tester->getStatusCode());
    }
}
