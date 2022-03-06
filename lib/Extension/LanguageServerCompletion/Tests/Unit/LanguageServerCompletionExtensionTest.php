<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests\Unit;

use Phpactor\LanguageServerProtocol\CompletionList;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use Phpactor\Extension\LanguageServerCompletion\Tests\IntegrationTestCase;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;

class LanguageServerCompletionExtensionTest extends IntegrationTestCase
{
    public function testComplete(): void
    {
        $tester = $this->createTester();

        $position = new Position(0, 0);
        $tester->textDocument()->open('/test', 'hello');

        $response = $tester->requestAndWait('textDocument/completion', [
            'textDocument' => new TextDocumentIdentifier('/test'),
            'position' => $position,
        ]);

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertNull($response->error);
        $this->assertInstanceOf(CompletionList::class, $response->result);
    }

    public function testSignatureProvider(): void
    {
        $tester = $this->createTester();

        $position = new Position(0, 0);
        $tester->textDocument()->open('/test', 'hello');

        $response = $tester->requestAndWait('textDocument/signatureHelp', [
            'textDocument' => new TextDocumentIdentifier('/test'),
            'position' => $position,
        ]);

        $this->assertInstanceOf(ResponseMessage::class, $response);
        $this->assertNull($response->error);
        $this->assertNull($response->result);
    }
}
