<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServerBridge\TextDocument\WorkspaceTextDocumentLocator;
use Phpactor\Extension\LanguageServerRename\Handler\FileRenameHandler;
use Phpactor\Rename\Model\FileRenamer\TestFileRenamer;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Extension\LanguageServerRename\Tests\IntegrationTestCase;
use Phpactor\Extension\LanguageServerRename\Util\LocatedTextEditConverter;
use Phpactor\LanguageServerProtocol\FileOperationRegistrationOptions;
use Phpactor\LanguageServerProtocol\FileRename;
use Phpactor\LanguageServerProtocol\RenameFilesParams;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Phpactor\LanguageServer\LanguageServerTesterBuilder;
use Phpactor\LanguageServer\Test\LanguageServerTester;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use function Amp\Promise\wait;

class FileRenameHandlerTest extends IntegrationTestCase
{
    public function testCapabilities(): void
    {
        $server = $this->createServer();
        $result = $server->initialize();

        self::assertInstanceOf(FileOperationRegistrationOptions::class, $result->capabilities->workspace['fileOperations']->willRename);
    }

    public function testMoveFileNoEdits(): void
    {
        $server = $this->createServer();
        $server->initialize();
        $response = wait($server->request('workspace/willRenameFiles', new RenameFilesParams([
            new FileRename('file:///file1', 'file:///file2'),
        ])));
        assert($response instanceof ResponseMessage);

        self::assertInstanceOf(WorkspaceEdit::class, $response->result);
    }

    public function testMoveFileEdits(): void
    {
        $server = $this->createServer(false, [
            'file:///file1' => TextEdits::one(TextEdit::create(0, 0, 'Hello')),
            'file:///file2' => TextEdits::one(TextEdit::create(0, 0, 'Hello')),
        ]);
        $server->initialize();

        $response = wait($server->request('workspace/willRenameFiles', new RenameFilesParams([
            new FileRename('file:///file1', 'file:///file2'),
        ])));

        assert($response instanceof ResponseMessage);

        $edits = $response->result;
        self::assertInstanceOf(WorkspaceEdit::class, $edits);
        assert($edits instanceof WorkspaceEdit);
        self::assertCount(2, $edits->documentChanges);
    }

    private function createServer(bool $willFail = false, array $workspaceEdits = []): LanguageServerTester
    {
        $builder = LanguageServerTesterBuilder::createBare()
            ->enableTextDocuments()
            ->enableFileEvents();
        $builder->addHandler($this->createHandler($builder, $willFail, $workspaceEdits));
        $server = $builder->build();

        foreach ($workspaceEdits as $path => $_) {
            $server->textDocument()->open($path, '');
        }
        return $server;
    }

    private function createHandler(LanguageServerTesterBuilder $builder, bool $willError = false, array $workspaceEdits = []): FileRenameHandler
    {
        return new FileRenameHandler(
            new TestFileRenamer($willError, new LocatedTextEditsMap($workspaceEdits)),
            new LocatedTextEditConverter($builder->workspace(), new WorkspaceTextDocumentLocator($builder->workspace()))
        );
    }
}
