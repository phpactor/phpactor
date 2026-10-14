<?php

namespace Phpactor\Extension\LanguageServerRename\Handler;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\FileOperationOptions;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\Extension\LanguageServerRename\Util\WorkspaceOperationsConverter;
use Phpactor\LanguageServerProtocol\FileOperationFilter;
use Phpactor\LanguageServerProtocol\FileOperationPattern;
use Phpactor\LanguageServerProtocol\FileOperationRegistrationOptions;
use Phpactor\LanguageServerProtocol\RenameFilesParams;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\Rename\Model\WorkspaceOperations;
use Phpactor\TextDocument\TextDocumentUri;
use function Amp\call;

class FileRenameHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private FileRenamer $renamer,
        private WorkspaceOperationsConverter $converter,
        private ClientApi $clientApi,
    ) {
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
            try {
                $workspaceEdits = new WorkspaceOperations([]);
                foreach ($params->files as $rename) {
                    $workspaceEdits = $workspaceEdits->merge(yield $this->renamer->renameFile(
                        TextDocumentUri::fromString($rename->oldUri),
                        TextDocumentUri::fromString($rename->newUri)
                    ));
                }

                return $this->converter->toWorkspaceEdit($workspaceEdits);
            } catch (CouldNotRename $error) {
                $previous = $error->getPrevious();

                $this->clientApi->window()->showMessage()->error(sprintf(
                    '%s %s',
                    $error->getMessage(),
                    $previous?->getTraceAsString() ?? '',
                ));

                return new WorkspaceEdit(null, []);
            }
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
