<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\CreateFile;
use Phpactor\LanguageServerProtocol\CreateFileOptions;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class CreateClassCommand implements Command
{
    public const NAME  = 'create_class';

    public function __construct(
        private readonly ClientApi $clientApi,
        private readonly Workspace $workspace,
        private readonly Generators $generators,
        private readonly FileToClass $fileToClass
    ) {
    }

    /**
     * @return Promise<ApplyWorkspaceEditResult>
     */
    public function __invoke(string $uri, string $transform): Promise
    {
        $documentChanges = [];
        if (!$this->workspace->has($uri)) {
            $textDocument = new TextDocumentItem($uri, 'php', 0, '');
            $documentChanges[] = new CreateFile('create', $uri, new CreateFileOptions(false, true));
        } else {
            $textDocument = $this->workspace->get($uri);
        }
        $generator = $this->generators->get($transform);
        assert($generator instanceof GenerateNew);

        $className = $this->fileToClass->fileToClassCandidates(
            FilePath::fromString(TextDocumentUri::fromString($uri)->path())
        );

        $sourceCode = $generator->generateNew(ClassName::fromString($className->best()->__toString()));
        $textEdits = TextEdits::one(
            TextEdit::create(0, PHP_INT_MAX, $sourceCode->__toString())
        );

        $message = 'Class created';
        if (count($documentChanges)) {
            $message = sprintf('Class registered at "%s"', $uri);
        }

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), $message);
    }
}
