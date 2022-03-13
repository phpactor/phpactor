<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\Transformers;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentUri;

class TransformCommand implements Command
{
    public const NAME  = 'transform';

    private Transformers $transformers;

    private Workspace $workspace;

    private ClientApi $clientApi;

    public function __construct(
        ClientApi $clientApi,
        Workspace $workspace,
        Transformers $transformers
    ) {
        $this->transformers = $transformers;
        $this->workspace = $workspace;
        $this->clientApi = $clientApi;
    }

    public function __invoke(string $uri, string $transform): Promise
    {
        $textDocument = $this->workspace->get($uri);
        $transformer = $this->transformers->get($transform);
        assert($transformer instanceof Transformer);
        $textEdits = $transformer->transform(
            SourceCode::fromStringAndPath(
                $textDocument->text,
                TextDocumentUri::fromString($textDocument->uri)->path()
            ),
        );

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), 'Apply source code transformation');
    }
}
