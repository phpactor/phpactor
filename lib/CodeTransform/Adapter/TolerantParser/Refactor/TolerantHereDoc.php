<?php

declare(strict_types=1);

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Phpactor\WorseReflection\Core\AstProvider;
use Microsoft\PhpParser\Node\StringLiteral;
use Phpactor\CodeTransform\Domain\Refactor\ByteOffsetRefactor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\TokenKind;

class TolerantHereDoc implements ByteOffsetRefactor
{
    public function __construct(private AstProvider $parser)
    {
    }

    public function refactor(TextDocument $document, ByteOffset $offset): TextEdits
    {
        $node = $this->parser
            ->get($document)
            ->getDescendantNodeAtPosition($offset->toInt())
        ;

        // If we're inside a variable inside a string literal, get the surrounding string
        if (!$node instanceof StringLiteral) {
            $node = $node->getFirstAncestor(StringLiteral::class);
        }

        if (!$node instanceof StringLiteral) {
            return TextEdits::none();
        }

        if ($node->startQuote instanceof Token && $node->startQuote->kind === TokenKind::HeredocStart) {
            if ($node->endQuote instanceof MissingToken) {
                return TextEdits::none();
            }
            return $this->convertFromHereDocToString($node);
        }

        return $this->convertFromStringToHereDoc($node);
    }

    private function convertFromStringToHereDoc(StringLiteral $node): TextEdits
    {
        // Trimming the quotes
        $content = $node->getStringContentsText();

        return TextEdits::fromTextEdits([TextEdit::create(
            $node->getStartPosition(),
            $node->getEndPosition() - $node->getStartPosition(),
            '<<<EOF'.PHP_EOL.$content.PHP_EOL.'EOF'
        )]);

    }

    private function convertFromHereDocToString(StringLiteral $node): TextEdits
    {
        $hereDocContent = trim($node->getStringContentsText());
        $hereDocContent = str_replace('"', '\\"', $hereDocContent);

        return TextEdits::fromTextEdits([TextEdit::create(
            $node->getStartPosition(),
            $node->getEndPosition() - $node->getStartPosition(),
            '"'.$hereDocContent.'"',
        )]);
    }
}
