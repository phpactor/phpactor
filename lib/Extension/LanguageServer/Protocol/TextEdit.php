<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class TextEdit
{
    /**
     * @var Range
     */
    public $range;

    /**
     * @var string
     */
    public $newText = '';

    public function __construct(Range $range, string $newText)
    {
        $this->range = $range;
        $this->newText = $newText;
    }
}
