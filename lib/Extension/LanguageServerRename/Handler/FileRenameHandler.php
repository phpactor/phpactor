<?php

namespace Phpactor\Extension\LanguageServerRename\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerRename\Model\FileRenamer;
use Phpactor\Extension\LanguageServerRename\Model\LocatedTextEditsMap;
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

class FileRenameHandler implements Handler, CanRegisterCapabilities
{
    /**
     * @var FileRenamer
     */
    private $renamer;

    /**
     * @var LocatedTextEditConverter
     */
    private $converter;

    public function __construct(FileRenamer $renamer, LocatedTextEditConverter $converter)
    {
        $this->renamer = $renamer;
        $this->converter = $converter;
    }

    /**
     * {@inheritDoc}
     */
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
            $workspaceEdits = LocatedTextEditsMap::create();
            foreach ($params->files as $rename) {
                assert($rename instanceof FileRename);

                $workspaceEdits = $workspaceEdits->merge(yield $this->renamer->renameFile(TextDocumentUri::fromString($rename->oldUri), TextDocumentUri::fromString($rename->newUri)));
            }

            return $this->converter->toWorkspaceEdit($workspaceEdits);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->workspace['fileOperations']['willRename'] = new FileOperationRegistrationOptions([
            new FileOperationFilter(
                new FileOperationPattern('**/*.php')
            )
        ]);
    }
}
