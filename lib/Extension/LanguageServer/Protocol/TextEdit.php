<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

use Phpactor\Extension\LanguageServer\Protocol\Range;

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
