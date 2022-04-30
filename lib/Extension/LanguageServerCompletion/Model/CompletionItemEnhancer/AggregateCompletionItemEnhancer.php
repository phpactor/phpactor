<?php

namespace Phpactor\Extension\LanguageServerCompletion\Model\CompletionItemEnhancer;

use Phpactor\Extension\LanguageServerCompletion\Model\CompletionItemEnhancer;
use Phpactor\LanguageServerProtocol\CompletionItem;

class AggregateCompletionItemEnhancer implements CompletionItemEnhancer
{
    /**
     * @var CompletionItemEnhancer[]
     */
    private array $enhancers;

    /**
     * @param CompletionItemEnhancer[] $enhancers
     */
    public function __construct(array $enhancers)
    {
        $this->enhancers = $enhancers;
    }

    public function enhance(CompletionItem $item, EnhancerContext $context): CompletionItem
    {
        foreach ($this->enhancers as $enhancer) {
            $item = $enhancer->enhance($item, $context);
        }

        return $item;
    }
}
