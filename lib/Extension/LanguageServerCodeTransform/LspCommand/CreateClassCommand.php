<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResponse;
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

    private ClientApi $clientApi;

    private Workspace $workspace;

    private Generators $generators;

    private FileToClass $fileToClass;

    public function __construct(
        ClientApi $clientApi,
        Workspace $workspace,
        Generators $generators,
        FileToClass $fileToClass
    ) {
        $this->clientApi = $clientApi;
        $this->workspace = $workspace;
        $this->generators = $generators;
        $this->fileToClass = $fileToClass;
    }

    /**
     * @return Promise<ApplyWorkspaceEditResponse>
     */
    public function __invoke(string $uri, string $transform): Promise
    {
        $textDocument = $this->workspace->get($uri);
        $generator = $this->generators->get($transform);
        assert($generator instanceof GenerateNew);

        $className = $this->fileToClass->fileToClassCandidates(
            FilePath::fromString(TextDocumentUri::fromString($uri)->path())
        );

        $sourceCode = $generator->generateNew(ClassName::fromString($className->best()->__toString()));
        $textEdits = TextEdits::one(
            TextEdit::create(0, PHP_INT_MAX, $sourceCode->__toString())
        );

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), 'Create class');
    }
}
