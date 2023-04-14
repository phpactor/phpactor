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
use function Amp\call;

class TransformCommand implements Command
{
    public const NAME  = 'transform';

    public function __construct(
        private ClientApi $clientApi,
        private Workspace $workspace,
        private Transformers $transformers
    ) {
    }

    /**
     * @return Promise<WorkspaceEdit[]>
     */
    public function __invoke(string $uri, string $transform): Promise
    {
        return call(function () use ($uri, $transform) {
            $textDocument = $this->workspace->get($uri);
            $transformer = $this->transformers->get($transform);
            assert($transformer instanceof Transformer);
            $textEdits = yield $transformer->transform(
                SourceCode::fromStringAndPath(
                    $textDocument->text,
                    $textDocument->uri
                ),
            );

            return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
                $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
            ]), 'Apply source code transformation');
        });
    }
}
