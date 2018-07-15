<?php

namespace Phpactor\Extension\LanguageServer\Response;

class Position
{
    /**
     * @var int
     */
    public $line;

    /**
     * @var int
     */
    public $character;

    public function __construct(int $line, int $character)
    {
        $this->line = $line;
        $this->character = $character;
    }
}
