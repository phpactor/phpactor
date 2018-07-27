<?php

namespace Phpactor\Extension\LanguageServer\Server\Method\TextDocument;

use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\LanguageServer\Protocol\CompletionItem;
use Phpactor\Extension\LanguageServer\Protocol\CompletionList;
use Phpactor\Extension\LanguageServer\Protocol\Position;
use Phpactor\Extension\LanguageServer\Protocol\Range;
use Phpactor\Extension\LanguageServer\Protocol\TextEdit;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\Extension\LanguageServer\Protocol\TextDocumentItem;
use Phpactor\Extension\LanguageServer\Server\Workspace;
use Phpactor\Extension\LanguageServer\Util\OffsetHelper;

class Completion implements Method
{
    /**
     * @var Workspace
     */
    private $workspace;

    /**
     * @var Completor
     */
    private $completor;

    public function __construct(Completor $completor, Workspace $workspace)
    {
        $this->workspace = $workspace;
        $this->completor = $completor;
    }

    public function name(): string
    {
        return 'textDocument/completion';
    }

    public function __invoke(TextDocumentItem $textDocument, Position $position): CompletionList
    {
        $textDocument = $this->workspace->get($textDocument->uri);

        $offset = OffsetHelper::lineAndCharacterNumberToOffset(
            $textDocument->text,
            $position->line,
            $position->character
        );

        $response = $this->completor->complete($textDocument->text, $offset);
        $suggestions = $response->suggestions();

        $completionList = new CompletionList();

        /** @var Suggestion $suggestion */
        foreach ($suggestions as $suggestion) {
            $item = new CompletionItem($suggestion->name());
            $item->insertText = $suggestion->name();
            $item->detail = $suggestion->info();
            $item->documentation = $suggestion->info();
            $item->kind = null;

            $item->textEdit = new TextEdit(
                new Range($position, $position),
                $suggestion->name()
            );

            $completionList->items[] = $item;
        }

        return $completionList;
    }
}
