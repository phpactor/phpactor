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
    public $isIncomplete;

    /**
     * The completion items
     *
     * @var CompletionItem[]
     */
    public $items = [];

    public function __construct(array $items, bool $isIncomplete = false)
    {
        $this->items = $items;
        $this->isIncomplete = $isIncomplete;
    }
}
