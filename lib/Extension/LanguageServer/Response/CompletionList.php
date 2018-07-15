<?php

namespace Phpactor\Extension\LanguageServer\Response;

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
     * @var array
     */
    public $items = [];
}
