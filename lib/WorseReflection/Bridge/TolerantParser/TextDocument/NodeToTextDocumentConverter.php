<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\TextDocument;

use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentBuilder;

final class NodeToTextDocumentConverter
{
    public static function convert(Node $node): TextDocument
    {
        $document = TextDocumentBuilder::create($node->getFileContents());
        $uri = $node->getUri();

        if ($uri) {
            $document->uri($uri);
        }

        return $document->build();
    }
}
