<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\NameImporter;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class ImportNameCommand implements Command
{
    public const NAME = 'name_import';

    public function __construct(
        private readonly NameImporter $nameImporter,
        private readonly Workspace $workspace,
        private readonly ClientApi $client
    ) {
    }

    public function __invoke(
        string $uri,
        int $offset,
        string $type,
        string $fqn,
        ?string $alias = null
    ): Promise {
        $document = $this->workspace->get($uri);
        $result = $this->nameImporter->__invoke($document, $offset, $type, $fqn, true, $alias);

        if ($result->isSuccess()) {
            if (!$result->hasTextEdits()) {
                return new Success(null);
            }

            $textEdits = $result->getTextEdits();
            return $this->client->workspace()->applyEdit(new WorkspaceEdit([
                $uri => $textEdits
            ]), 'Import class');
        }

        $error = $result->getError();
        $this->client->window()->showMessage()->warning($error->getMessage());
        return new Success(null);
    }
}
