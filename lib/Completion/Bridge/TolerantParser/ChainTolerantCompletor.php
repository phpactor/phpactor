<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Core\Util\OffsetHelper;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class ChainTolerantCompletor implements Completor
{
    /**
     * @param TolerantCompletor[] $tolerantCompletors
     */
    public function __construct(
        private readonly array $tolerantCompletors,
        private readonly AstProvider $parser = new TolerantAstProvider(),
    ) {
    }

    /**
     * @return Generator<Suggestion>
     */
    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $truncatedSource = $this->truncateSource((string) $source, $byteOffset->toInt());
        $truncatedLength = strlen($truncatedSource);

        // gh-3001: this is potentially very inefficient
        $node = $this->parser
            ->get(TextDocumentBuilder::create(
                substr_replace((string) $source, ' ', $truncatedLength, 0)
            )->build())
            ->getDescendantNodeAtPosition(
                // the parser requires the byte offset, not the char offset
                $truncatedLength,
            );

        if ($node->getEndPosition() > $truncatedLength) {
            $node = $this->parser->get(
                TextDocumentBuilder::create($truncatedSource)->build()
            )->getDescendantNodeAtPosition($truncatedLength);
        }

        $isComplete = true;

        foreach ($this->tolerantCompletors as $tolerantCompletor) {
            $completionNode = $node;

            if ($tolerantCompletor instanceof TolerantQualifiable) {
                $completionNode = $tolerantCompletor->qualifier()->couldComplete($node);
            }

            if (!$completionNode) {
                continue;
            }

            $suggestions = $tolerantCompletor->complete($completionNode, $source, $byteOffset);

            yield from $suggestions;

            $isComplete = $isComplete && $suggestions->getReturn();
        }

        return $isComplete;
    }

    private function truncateSource(string $source, int $byteOffset): string
    {
        // truncate source at the byte offset - we don't want the rest of the source
        // file contaminating the completion (for example `$foo($<>\n    $bar =
        // ` will evaluate the Variable node as an expression node with a
        // double variable `$\n    $bar = `
        $truncatedSource = substr($source, 0, $byteOffset);

        // determine the last non-whitespace _character_ offset
        $characterOffset = OffsetHelper::lastNonWhitespaceCharacterOffset($truncatedSource);

        $lastChar = mb_substr($source, $characterOffset - 1, 1);

        // truncate the source at the character offset
        $truncatedSource = mb_substr($source, 0, $characterOffset);

        return $truncatedSource;
    }

    private function filterNonQualifyingClasses(Node $node): array
    {
        return array_filter($this->tolerantCompletors, function (TolerantCompletor $completor) use ($node) {
            if (!$completor instanceof TolerantQualifiable) {
                return true;
            }

            return null !== $completor->qualifier()->couldComplete($node);
        });
    }
}
