<?php

namespace Phpactor\Extension\CompletionRpc\Tests\Unit\Handler;

use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\TypedCompletorRegistry;
use Phpactor\Extension\CompletionRpc\Handler\CompleteHandler;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\Extension\Rpc\Test\HandlerTester;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;
use Prophecy\Prophecy\ObjectProphecy;

class CompleteHandlerTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $completor;

    public function setUp(): void
    {
        $this->completor = $this->prophesize(Completor::class);
        $this->registry = new TypedCompletorRegistry([
            'php' => $this->completor->reveal(),
        ]);
    }

    public function testHandler(): void
    {
        $handler = new CompleteHandler($this->registry);
        $this->completor->complete(
            TextDocumentBuilder::create('aaa')->language('php')->build(),
            ByteOffset::fromInt(1234)
        )->will(function () {
            yield Suggestion::create('aaa');
            yield Suggestion::create('bbb');
        });
        $action = (new HandlerTester($handler))->handle('complete', [
            'source' => 'aaa',
            'offset' => 1234
        ]);

        $this->assertInstanceOf(ReturnResponse::class, $action);
        $this->assertCount(2, $action->value()['suggestions']);
    }
}
