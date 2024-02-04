<?php

namespace Phpactor\Extension\LanguageServerRename\Handler;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\FileOperationOptions;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\LanguageServerProtocol\FileOperationFilter;
use Phpactor\LanguageServerProtocol\FileOperationPattern;
use Phpactor\LanguageServerProtocol\FileOperationRegistrationOptions;
use Phpactor\LanguageServerProtocol\FileRename;
use Phpactor\LanguageServerProtocol\RenameFilesParams;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\TextDocument\TextDocumentUri;
use function Amp\call;

class FileRenameHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(private FileRenamer $renamer)
    {
    }


    public function methods(): array
    {
        return [
            'workspace/willRenameFiles' => 'willRenameFiles'
        ];
    }

    /**
     * @return Promise<WorkspaceEdit>
     */
    public function willRenameFiles(RenameFilesParams $params): Promise
    {
        return call(function () use ($params) {
            $documentChanges = [];

            foreach ($params->files as $rename) {
                assert($rename instanceof FileRename);

                $res = yield $this->renamer->renameFile(
                    TextDocumentUri::fromString($rename->oldUri),
                    TextDocumentUri::fromString($rename->newUri)
                );

                foreach ($res->documentChanges as $change) {
                    $documentChanges[] = $change;
                }
            }

            return new WorkspaceEdit(documentChanges: $documentChanges);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->workspace['fileOperations'] = new FileOperationOptions(willRename: new FileOperationRegistrationOptions(
            filters: [
                new FileOperationFilter(
                    new FileOperationPattern(
                        glob: '**/*.php'
                    )
                ),
            ]
        ));
    }
}
