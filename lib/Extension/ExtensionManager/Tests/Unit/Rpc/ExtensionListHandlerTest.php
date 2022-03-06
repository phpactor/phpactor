<?php

namespace Phpactor\Extension\ExtensionManager\Tests\Unit\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Model\ExtensionRepository;
use Phpactor\Extension\ExtensionManager\Model\ExtensionState;
use Phpactor\Extension\ExtensionManager\Model\Extensions;
use Phpactor\Extension\ExtensionManager\Rpc\ExtensionListHandler;
use Phpactor\Extension\Rpc\Test\HandlerTester;

class ExtensionListHandlerTest extends TestCase
{
    public function testListsExtensions(): void
    {
        $repository = $this->prophesize(ExtensionRepository::class);
        $repository->extensions()->willReturn(new Extensions([
            new Extension('one', 'dev-xxx', ['class'], 'One', [], ExtensionState::STATE_PRIMARY),
            new Extension('two', 'dev-yyy', ['class'], 'Two', [], ExtensionState::STATE_SECONDARY),
            new Extension('three', 'dev-yyy', ['class'], 'Two'),
        ]));
        $tester = new HandlerTester(new ExtensionListHandler($repository->reveal()));
        $response = $tester->handle('extension_list', []);

        $this->assertEquals(<<<'EOT'
            [✔] one                            One (dev-xxx)
            [✔] two                            Two (dev-yyy)
            [ ] three                          Two
            EOT
        , $response->message());
    }
}
