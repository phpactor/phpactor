<?php

namespace Phpactor\Extension\LanguageServerBridge\Converter;

use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;

class TextDocumentConverter
{
    public static function fromLspTextItem(TextDocumentItem $item): TextDocument
    {
        $builder = TextDocumentBuilder::create($item->text);
        if ($item->uri) {
            $builder->uri($item->uri);
        }
        $builder->language($item->languageId);

        return $builder->build();
    }
}
