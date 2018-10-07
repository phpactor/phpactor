<?php

namespace Phpactor\Tests\Unit\Extension\LanguageServer\Extension;

use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;
use LanguageServerProtocol\TextDocumentContentChangeEvent;
use LanguageServerProtocol\TextDocumentItem;
use LanguageServerProtocol\VersionedTextDocumentIdentifier;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Extension\DidChangeHandler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\LanguageServer\Core\Transport\NotificationMessage;

class DidChangeHandlerTest extends TestCase
{
    /**
     * @var Manager
     */
    private $manager;
    /**
     * @var DidChangeHandler
     */
    private $handler;

    public function setUp()
    {
        $this->manager = new Manager('foo');
        $this->handler = new DidChangeHandler($this->manager);
    }

    public function testClearsDiagnostics()
    {
        $this->manager->initialize('foo');
        $this->manager->current()->workspace()->open(new TextDocumentItem('foo', 'bar'));

        $document = new VersionedTextDocumentIdentifier('foo', 0);
        $generator = $this->handler->__invoke($document, [
            new TextDocumentContentChangeEvent(new Range(new Position(0, 0), new Position(0, 0)))
        ]);

        $notification = $generator->current();
        $this->assertInstanceOf(NotificationMessage::class, $notification);
        assert($notification instanceof NotificationMessage);
        $this->assertEquals('textDocument/publishDiagnostics', $notification->method);
    }
}
