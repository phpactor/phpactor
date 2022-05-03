<?php

namespace Phpactor\Extension\LanguageServerBridge\Converter;

use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextEdit as LspTextEdit;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class TextEditConverter
{
    /**
     * @param TextEdits<TextEdit> $textEdits
     * @return array<LspTextEdit>
     */
    public static function toLspTextEdits(TextEdits $textEdits, string $text): array
    {
        $edits = [];
        foreach ($textEdits as $textEdit) {
            $range = new Range(
                PositionConverter::byteOffsetToPosition($textEdit->start(), $text),
                PositionConverter::byteOffsetToPosition($textEdit->end(), $text),
            );

            // deduplicate text edits
            $edits[] = new LspTextEdit($range, $textEdit->replacement());
        }

        return array_values($edits);
    }
}
