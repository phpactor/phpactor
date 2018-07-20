<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class CompletionOptions
{
    /**
     * @var bool
     */
    public $resolveProvider = false;

    /**
     * @var array
     */
    public $triggerCharacters = [];

    public function __construct(bool $resolveProvider = false, array $triggerCharacters = [])
    {
        $this->resolveProvider = $resolveProvider;
        $this->triggerCharacters = $triggerCharacters;
    }
}
