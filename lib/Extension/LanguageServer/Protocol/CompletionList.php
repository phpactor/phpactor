<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class CompletionList
{
    /**
     * This list it not complete. Further typing should result in recomputing
     * this list.
     *
     * @var bool
     */
    public $isIncomplete = false;

    /**
     * The completion items
     *
     * @var CompletionItem[]
     */
    public $items = [];
}
