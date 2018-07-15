<?php

namespace Phpactor\Extension\LanguageServer\Server\Protocol;

class TextDocument
{
    /**
     * @var string
     */
    public $languageId;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $uri;

    public function __construct(string $languageId, string $text, string $uri)
    {
        $this->languageId = $languageId;
        $this->text = $text;
        $this->uri = $uri;
    }
}
