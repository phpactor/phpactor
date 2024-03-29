<?php

namespace Phpactor\Extension\LanguageServerRename\Handler;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\LocatedTextEditsMap;
use Phpactor\Rename\Model\WorkspaceRenameEdits;
use Phpactor\Rename\Model\RenameResult;
use Phpactor\Rename\Model\Renamer;
use Phpactor\Extension\LanguageServerRename\Util\WorkspaceRenameEditsConverter;
use Phpactor\LanguageServerProtocol\PrepareRenameParams;
use Phpactor\LanguageServerProtocol\PrepareRenameRequest;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\RenameOptions;
use Phpactor\LanguageServerProtocol\RenameParams;
use Phpactor\LanguageServerProtocol\RenameRequest;
use Phpactor\LanguageServerProtocol\ServerCapabilities;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Handler\CanRegisterCapabilities;
use Phpactor\LanguageServer\Core\Handler\Handler;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;

use function Amp\delay;

class RenameHandler implements Handler, CanRegisterCapabilities
{
    public function __construct(
        private WorkspaceRenameEditsConverter $converter,
        private TextDocumentLocator $documentLocator,
        private Renamer $renamer,
        private ClientApi $clientApi
    ) {
    }


    /**
     * @return array<string,string>
     */
    public function methods(): array
    {
        return [
            PrepareRenameRequest::METHOD => 'prepareRename',
            RenameRequest::METHOD => 'rename',
        ];
    }

    /**
     * @return Promise<WorkspaceEdit>
     */
    public function rename(RenameParams $params): Promise
    {
        return \Amp\call(function () use ($params) {
            $locatedEdits = [];
            $document = $document = $this->documentLocator->get(TextDocumentUri::fromString($params->textDocument->uri));
            $count = 0;

            try {
                $rename = $this->renamer->rename(
                    $document,
                    PositionConverter::positionToByteOffset(
                        $params->position,
                        (string)$document
                    ),
                    $params->newName
                );
                foreach ($rename as $result) {
                    if ($count++ === 10) {
                        yield delay(1);
                    }
                    $locatedEdits[] = $result;
                }

                return $this->resultToWorkspaceEdit($locatedEdits, $rename->getReturn());
            } catch (CouldNotRename $error) {
                $previous = $error->getPrevious();

                $this->clientApi->window()->showMessage()->error(sprintf(
                    $error->getMessage() . ($previous?->getTraceAsString() ?? '')
                ));

                return new WorkspaceEdit(null, []);
            }
        });
    }

    /**
     * @return Promise<Range>
     */
    public function prepareRename(PrepareRenameParams $params): Promise
    {
        // https://microsoft.github.io/language-server-protocol/specification#textDocument_prepareRename
        return \Amp\call(function () use ($params) {
            $range = $this->renamer->getRenameRange(
                $document = $this->documentLocator->get(TextDocumentUri::fromString($params->textDocument->uri)),
                PositionConverter::positionToByteOffset(
                    $params->position,
                    (string)$document
                ),
            );
            if ($range == null) {
                return null;
            }
            return RangeConverter::toLspRange($range, (string)$document);
        });
    }

    public function registerCapabiltiies(ServerCapabilities $capabilities): void
    {
        $capabilities->renameProvider = new RenameOptions(true);
    }

    /**
     * @param LocatedTextEdit[] $locatedEdits
     */
    private function resultToWorkspaceEdit(array $locatedEdits, ?RenameResult $renameResult): WorkspaceEdit
    {
        return $this->converter->toWorkspaceEdit(new WorkspaceRenameEdits(
            LocatedTextEditsMap::fromLocatedEdits($locatedEdits),
            $renameResult,
        ));
    }
}
