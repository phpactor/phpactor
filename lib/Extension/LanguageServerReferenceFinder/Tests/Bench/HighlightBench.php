<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Bench;

use Phpactor\Extension\LanguageServerReferenceFinder\Model\Highlighter;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class HighlightBench
{
    public function benchHighlights(): void
    {
        $highlighter = new Highlighter(new \Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider());
        $highlights = $highlighter->highlightsFor(
            TextDocumentBuilder::fromUri(__DIR__ . '/../../../../../vendor/microsoft/tolerant-php-parser/src/Parser.php')->build(),
            ByteOffset::fromInt(176949)
        );
    }
}
