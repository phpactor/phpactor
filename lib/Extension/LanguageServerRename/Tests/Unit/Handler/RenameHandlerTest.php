<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Unit\Handler;

use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\Renamer\InMemoryRenamer;
use Phpactor\Extension\LanguageServerRename\Tests\IntegrationTestCase;
use Phpactor\LanguageServerProtocol\PrepareRenameParams;
use Phpactor\LanguageServerProtocol\PrepareRenameRequest;
use Phpactor\LanguageServerProtocol\RenameParams;
use Phpactor\LanguageServerProtocol\RenameRequest;
use Phpactor\LanguageServerProtocol\TextDocumentEdit;
use Phpactor\LanguageServerProtocol\TextEdit as PhpactorTextEdit;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;

class RenameHandlerTest extends IntegrationTestCase
{
    const EXAMPLE_FILE = 'file:///Foobar.php';
    const EXAMPLE_NEW_NAME = 'foobar';

    private LanguageServerTester $tester;

    private InMemoryRenamer $renamer;

    public function testRegistersCapabilities(): void
    {
        $this->bootContainerWithRangeAndResults(null, []);
        $result = $this->tester->initialize();
        self::assertTrue($result->capabilities->renameProvider->prepareProvider);
    }

    public function testPrepareRenameReturnsNullIfItCouldNotPrepareAnything(): void
    {
        $this->bootContainerWithRangeAndResults(null, []);
        $this->tester->textDocument()->open(self::EXAMPLE_FILE, '<?php');

        $response = $this->tester->requestAndWait(
            PrepareRenameRequest::METHOD,
            new PrepareRenameParams(
                ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_FILE),
                ProtocolFactory::position(0, 0),
            )
        );

        $this->tester->assertSuccess($response);
        self::assertNull($response->result);
    }

    public function testPrepareRename(): void
    {
        $expectedCharOffset = 3;

        $this->bootContainerWithRangeAndResults(ByteOffsetRange::fromInts(0, $expectedCharOffset), []);
        $this->tester->textDocument()->open(self::EXAMPLE_FILE, '<?php');

        $response = $this->tester->requestAndWait(
            PrepareRenameRequest::METHOD,
            new PrepareRenameParams(
                ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_FILE),
                ProtocolFactory::position(0, 0),
            )
        );

        $this->tester->assertSuccess($response);
        self::assertEquals(ProtocolFactory::range(0, 0, 0, $expectedCharOffset), $response->result);
    }

    public function testRename(): void
    {
        $expectedUri = TextDocumentUri::fromString(self::EXAMPLE_FILE);
        $this->bootContainerWithRangeAndResults(ByteOffsetRange::fromInts(0, 0), [
            new LocatedTextEdit(
                $expectedUri,
                TextEdit::create(ByteOffset::fromInt(1), 0, self::EXAMPLE_NEW_NAME)
            )
        ]);
        $this->tester->textDocument()->open(self::EXAMPLE_FILE, '<?php');

        $response = $this->tester->requestAndWait(
            RenameRequest::METHOD,
            new RenameParams(
                ProtocolFactory::textDocumentIdentifier(self::EXAMPLE_FILE),
                ProtocolFactory::position(0, 0),
                self::EXAMPLE_NEW_NAME
            )
        );

        $this->tester->assertSuccess($response);
        assert($response->result instanceof WorkspaceEdit);
        self::assertNull($response->result->changes);
        $edit = $response->result->documentChanges[0];
        assert($edit instanceof TextDocumentEdit);
        self::assertEquals(self::EXAMPLE_FILE, $edit->textDocument->uri);
        $edit = reset($edit->edits);
        assert($edit instanceof PhpactorTextEdit);
        self::assertEquals(self::EXAMPLE_NEW_NAME, $edit->newText);
    }

    protected function bootContainerWithRangeAndResults(?ByteOffsetRange $range, array $results): void
    {
        $container = $this->container([
            'range' => $range,
            'results' => $results,
        ]);
        $this->tester = $container->get(LanguageServerBuilder::class)->tester(
            ProtocolFactory::initializeParams($this->workspace()->path())
        );
        $this->renamer = $container->get(InMemoryRenamer::class);
    }
}
