<?php

namespace Phpactor\Extension\LanguageServerRename\Handler;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\FileOperationOptions;
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
    public function __construct(private FileRenamer $renamer, private LocatedTextEditConverter $converter)
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
            $count = 0;
            $documentChanges = [];

            foreach ($params->files as $rename) {
                $locatedEditMap = LocatedTextEditsMap::create();
                assert($rename instanceof FileRename);

                $renameGen = $this->renamer->renameFile(
                    TextDocumentUri::fromString($rename->oldUri),
                    TextDocumentUri::fromString($rename->newUri)
                );

                foreach ($renameGen as $locatedTextEdit) {
                    if ($count++ === 10) {
                        yield delay(1);
                    }
                    $locatedEditMap->withTextEdit($locatedTextEdit);
                }

                $workspaceEdit = $this->converter->toWorkspaceEdit($locatedEditMap, $renameGen->getReturn());

                foreach ($workspaceEdit->documentChanges as $change) {
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
