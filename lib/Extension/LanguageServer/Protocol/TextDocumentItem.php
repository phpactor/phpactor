<?php

namespace Phpactor\Extension\LanguageServer\Protocol;

class TextDocumentItem
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

    /**
     * @var string
     */
    public $version;

    public function __construct(string $uri, string $text = null, string $languageId = null, string $version = null)
    {
        $this->languageId = $languageId;
        $this->text = $text;
        $this->uri = $uri;
        $this->version = $version;
    }
}
