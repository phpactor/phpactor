<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Bench;

use Microsoft\PhpParser\Parser;
use Phpactor\Extension\LanguageServerReferenceFinder\Adapter\TolerantHighlighter;
use Phpactor\TextDocument\ByteOffset;

class HighlightBench
{
    public function benchHighlights(): void
    {
        $highlighter = new TolerantHighlighter(new Parser());
        $highlights = $highlighter->highlightsFor(
            file_get_contents(__DIR__ . '/../../../../../vendor/microsoft/tolerant-php-parser/src/Parser.php'),
            ByteOffset::fromInt(176949)
        );
    }
}
