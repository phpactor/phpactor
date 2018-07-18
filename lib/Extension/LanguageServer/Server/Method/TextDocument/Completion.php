<?php

namespace Phpactor\Extension\LanguageServer\Server\Method\TextDocument;

use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Completion\Application\Complete;
use Phpactor\Extension\LanguageServer\Protocol\CompletionItem;
use Phpactor\Extension\LanguageServer\Protocol\CompletionList;
use Phpactor\Extension\LanguageServer\Protocol\Position;
use Phpactor\Extension\LanguageServer\Protocol\Range;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\Extension\LanguageServer\Protocol\TextDocument;
use Phpactor\Extension\LanguageServer\Server\Workspace;
use Phpactor\MapResolver\Resolver;

class Completion implements Method
{
    /**
     * @var Workspace
     */
    private $workspace;

    public function __construct(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function name(): string
    {
        return 'textDocument/didOpen';
    }

    public function __invoke(TextDocument $textDocument, Position $position): CompletionList
    {
        $offset = null;
        $textDocument = $this->workspace->get($textDocument->uri);
        $response = $this->completor->complete($textDocument->text, $offset);

        $suggestions = $response->suggestions();

        $completionList = new CompletionList();

        /** @var Suggestion $suggestion */
        foreach ($suggestions as $suggestion) {

            $item = new CompletionItem();
            $item->label = $suggestion->info();
            $item->textEdit = new TextEdit(
                new Range($position, $position),
                $suggestion->name()
            );

            $completionList->items[] = $item;

        }

        return $completionList;
    }
}
