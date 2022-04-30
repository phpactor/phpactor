<?php

namespace Phpactor\Extension\LanguageServerCompletion\Model;
use Phpactor\Extension\LanguageServerCompletion\Model\CompletionItemEnhancer\EnhancerContext;

use Phpactor\LanguageServerProtocol\CompletionItem;

interface CompletionItemEnhancer
{
    public function enhance(CompletionItem $item, EnhancerContext $context): CompletionItem;
}
