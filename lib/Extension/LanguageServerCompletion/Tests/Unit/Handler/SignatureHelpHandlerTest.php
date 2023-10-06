<?php

namespace Phpactor\Extension\LanguageServerCompletion\Tests\Unit\Handler;

use Phpactor\LanguageServerProtocol\SignatureHelp as LspSignatureHelp;
use Phpactor\LanguageServerProtocol\TextDocumentIdentifier;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\SignatureHelp;
use Phpactor\Completion\Core\SignatureHelper;
use Phpactor\Extension\LanguageServerCompletion\Handler\SignatureHelpHandler;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class SignatureHelpHandlerTest extends TestCase
{
    private const IDENTIFIER = '/test';

    public function testHandleHelpers(): void
    {
        $tester = $this->create([]);
        $tester->textDocument()->open(self::IDENTIFIER, 'hello');
        $response = $tester->requestAndWait(
            'textDocument/signatureHelp',
            [
                'textDocument' => new TextDocumentIdentifier(self::IDENTIFIER),
                'position' => ProtocolFactory::position(0, 0)
            ]
        );
        $list = $response->result;
        $this->assertInstanceOf(LspSignatureHelp::class, $list);
    }

    private function create(array $suggestions): LanguageServerTester
    {
        $builder = LanguageServerTesterBuilder::create();
        return $builder->addHandler(new SignatureHelpHandler(
            $builder->workspace(),
            $this->createHelper()
        ))->build();
    }

    private function createHelper(): SignatureHelper
    {
        return new class() implements SignatureHelper {
            public function signatureHelp(TextDocument $textDocument, ByteOffset $offset): SignatureHelp
            {
                $help = new SignatureHelp([], 0);
                return $help;
            }
        };
    }
}
