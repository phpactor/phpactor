<?php
declare(strict_types=1);

namespace Phpactor\Extension\PHPUnit\LspCommand;

use Amp\Promise;
use LanguageServerProtocol\WorkspaceEdit;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\PHPUnit\CodeTransform\GenerateTestMethods;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;

class GenerateTestMethodCommand implements Command
{
    public const NAME = 'generate_test_methods';

    public function __construct(
        private ClientApi $clientApi,
        private Workspace $workspace,
        private GenerateTestMethods $generateTestMethods,
    ) {
    }

    /**
     * @return Promise<ApplyWorkspaceEditResult>
     */
    public function __invoke(string $uri, string $method): Promise
    {
        $textDocument = $this->workspace->get($uri);
        $source = SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri);

        $textEdits = $this->generateTestMethods->generateMethod($source, $method);

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), 'Generate decoration');
    }
}
