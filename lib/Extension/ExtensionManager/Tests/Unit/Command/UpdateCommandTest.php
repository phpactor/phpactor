<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Command\UpdateCommand;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Symfony\Component\Console\Tester\CommandTester;

class UpdateCommandTest extends TestCase
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
        $this->tester = new CommandTester(new UpdateCommand($this->installer->reveal()));
    }

    public function testItCallsTheUpdateer(): void
    {
        $this->installer->installForceUpdate()->shouldBeCalled();

        $this->tester->execute([]);
        $this->assertEquals(0, $this->tester->getStatusCode());
    }
}
