<?php

namespace Phpactor\Extension\Completion\LanguageServer;

use Generator;
use LanguageServerProtocol\CompletionItem;
use LanguageServerProtocol\CompletionList;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentItem;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;

class CompletionHandler implements Handler
{
    /**
     * @var Completor
     */
    private $completor;

    /**
     * @var Manager
     */
    private $sessionManager;

    public function __construct(Manager $sessionManager, Completor $completor)
    {
        $this->completor = $completor;
        $this->sessionManager = $sessionManager;
    }

    public function name(): string
    {
        return 'textDocument/completion';
    }

    public function __invoke(TextDocumentItem $textDocument, Position $position): Generator
    {
        $textDocument = $this->sessionManager->current()->workspace()->get($textDocument->uri);

        $suggestions = $this->completor->complete(
            $textDocument->text,
            $position->toOffset($textDocument->text)
        );

        $completionList = new CompletionList();
        $completionList->isIncomplete = true;

        foreach ($suggestions as $suggestion) {
            /** @var Suggestion $suggestion */
            $completionList->items[] = new CompletionItem(
                $suggestion->name(),
                PhpactorToLspCompletionType::fromPhpactorType($suggestion->type()),
                $suggestion->shortDescription()
            );

        }

        yield $completionList;
    }
}
