<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\GenerateMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResponse;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Exception\NotFound;

class GenerateMethodCommand implements Command
{
    public const NAME  = 'generate_method';

    private ClientApi $clientApi;

    private GenerateMethod $generateMethod;

    private Workspace $workspace;

    private TextDocumentLocator $locator;

    public function __construct(
        ClientApi $clientApi,
        Workspace $workspace,
        GenerateMethod $generateMethod,
        TextDocumentLocator $locator
    ) {
        $this->clientApi = $clientApi;
        $this->generateMethod = $generateMethod;
        $this->workspace = $workspace;
        $this->locator = $locator;
    }

    /**
     * @return Promise<?ApplyWorkspaceEditResponse>
     */
    public function __invoke(string $uri, int $offset): Promise
    {
        $document = $this->workspace->get($uri);
        $sourceCode = SourceCode::fromStringAndPath(
            $document->text,
            TextDocumentUri::fromString($document->uri)->path()
        );

        $textEdits = null;
        try {
            $textEdits = $this->generateMethod->generateMethod($sourceCode, $offset);
        } catch (TransformException $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        } catch (NotFound $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        }

        return $this->clientApi->workspace()->applyEdit(
            new WorkspaceEdit([
                $textEdits->uri()->__toString() => TextEditConverter::toLspTextEdits(
                    $textEdits->textEdits(),
                    $this->locator->get($textEdits->uri())->__toString()
                )
            ]),
            'Generate method'
        );
    }
}
