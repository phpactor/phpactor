<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Command;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Command\ListCommand;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\Extensions;
use Phpactor\Extension\ExtensionManager\Service\ExtensionLister;
use Symfony\Component\Console\Tester\CommandTester;

class ListCommandTest extends TestCase
{
    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var ObjectProphecy
     */
    private $lister;

    public function setUp(): void
    {
        $this->lister = $this->prophesize(ExtensionLister::class);
        $this->tester = new CommandTester(new ListCommand($this->lister->reveal()));
    }

    public function testListsAllExtensions(): void
    {
        $this->lister->list(false)->willReturn(new Extensions([
            new Extension('one', 'dev-xxx', ['class'], 'One'),
            new Extension('two', 'dev-yyy', ['class'], 'Two'),
        ]));

        $this->tester->execute([]);
        $this->assertEquals(0, $this->tester->getStatusCode());
    }

    public function testListsInstalledExtensions(): void
    {
        $this->lister->list(true)->willReturn(new Extensions([
            new Extension('one', 'dev-xxx', ['class'], 'One'),
            new Extension('two', 'dev-yyy', ['class'], 'Two')
        ]));

        $this->tester->execute([
            '--installed' => true,
        ]);
        $this->assertEquals(0, $this->tester->getStatusCode());
    }
}
