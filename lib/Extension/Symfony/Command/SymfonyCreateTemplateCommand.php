<?php

namespace Phpactor\Extension\Symfony\Command;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\Extension\Symfony\Model\SymfonyTemplateCache;
use Phpactor\FilePathResolver\PathResolver;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\CreateFile;
use Phpactor\LanguageServerProtocol\CreateFileOptions;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextEdits;

final class SymfonyCreateTemplateCommand implements Command
{
    public const NAME  = 'extract_method';

    public function __construct(
        private ClientApi $clientApi,
        private Workspace $workspace,
        private PathResolver $pathResolver,
        private Generators $generators,
        private SymfonyTemplateCache $symfonyTemplateCache,
    ) {
    }

    /**
     * @return Promise<ApplyWorkspaceEditResult>
     */
    public function __invoke(string $templateName): Promise
    {
        // TODO: move the creation to the parent class probably
        $uri = sprintf(
            'file://%s/%s/%s',
            $this->pathResolver->resolve('%project_root%'),
            SymfonyTemplateCache::TEMPLATE_FOLDER,
            $templateName,
        );

        $documentChanges = [];
        if (!$this->workspace->has($uri)) {
            $textDocument = new TextDocumentItem($uri, 'twig', 0, '');
            $documentChanges[] = new CreateFile('create', $uri, new CreateFileOptions(false, true));
        } else {
            $textDocument = $this->workspace->get($uri);
        }

        $message = 'Template created';
        if (count($documentChanges)) {
            $message = sprintf('Template registered at "%s"', $uri);
        }

        $textEdits = TextEdits::none();

        $this->symfonyTemplateCache->addTemplate($templateName);

        return $this->clientApi->workspace()->applyEdit(
            new WorkspaceEdit(
                [$uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)]
            ),
            $message
        );
    }
}
