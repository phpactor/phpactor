<?php

namespace Phpactor\Extension\LanguageServerRename\Handler;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\FileOperationOptions;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\FileRenamer;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Extension\LanguageServerRename\Util\LocatedTextEditConverter;
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
use function Amp\delay;

class FileRenameHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private FileRenamer $renamer,
        private LocatedTextEditConverter $converter,
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
            $count = 0;
            $documentChanges = [];
            try {
                foreach ($params->files as $rename) {
                    $locatedEditMap = LocatedTextEditsMap::create();

                    $$locatedTextEdits = $this->renamer->renameFile(
                        TextDocumentUri::fromString($rename->oldUri),
                        TextDocumentUri::fromString($rename->newUri)
                    );

                    foreach ($$locatedTextEdits as $locatedTextEdit) {
                        if ($count++ === 10) {
                            yield delay(1);
                        }
                        $locatedEditMap = $locatedEditMap->withTextEdit($locatedTextEdit);
                    }

                    $workspaceEdit = $this->converter->toWorkspaceEdit($locatedEditMap, $$locatedTextEdits->getReturn());

                    foreach ($workspaceEdit->documentChanges ?? [] as $change) {
                        $documentChanges[] = $change;
                    }
                }

                return new WorkspaceEdit(documentChanges: $documentChanges);
            } catch (CouldNotRename $error) {
                $previous = $error->getPrevious();

                $this->clientApi->window()->showMessage()->error(sprintf(
                    $error->getMessage() . ($previous?->getTraceAsString() ?? '')
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
