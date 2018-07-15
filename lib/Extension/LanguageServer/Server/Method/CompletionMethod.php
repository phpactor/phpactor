<?php

namespace Phpactor\Extension\LanguageServer\Server\Method;

use Phpactor\CodeBuilder\Adapter\TolerantParser\TextEdit;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Completion\Application\Complete;
use Phpactor\Extension\LanguageServer\Response\CompletionItem;
use Phpactor\Extension\LanguageServer\Response\CompletionList;
use Phpactor\Extension\LanguageServer\Response\Range;
use Phpactor\Extension\LanguageServer\Server\Method;
use Phpactor\MapResolver\Resolver;
use Phpactor\WorseReflection\Core\Position;

class CompletionMethod implements Method
{
    /**
     * @var Completor
     */
    private $completor;

    public function __construct(Completor $completor)
    {
        $this->completor = $completor;
    }

    public function name(): string
    {
        return 'textDocument/completion';
    }

    public function __invoke(array $params): CompletionList
    {
        $offset = null;
        $response = $this->completor->complete(file_get_contents($params['textDocument']['uri']), $offset);
        $suggestions = $response->suggestions();
        $completionList = new CompletionList();

        /** @var Suggestion $suggestion */
        foreach ($suggestions as $suggestion) {
            $item = new CompletionItem();
            $item->label = $suggestion->info();
            $item->textEdit = new TextEdit(
                new Range(
                    new Position(
                        $params['position']['line'],
                        $params['position']['character']
                    ),
                    new Position(
                        $params['position']['line'],
                        $params['position']['character']
                    )
                ),
                $suggestion->name()
            );

            $completionList->items[] = $item;
        }

        return $completionList;
    }

    public function configure(Resolver $resolver)
    {
        $resolver->setRequired([
            'position',
            'textDocument',
        ]);
    }
}
