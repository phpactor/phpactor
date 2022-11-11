<?php

namespace Phpactor\Completion\Bridge\TolerantParser;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Util\OffsetHelper;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ChainTolerantCompletor implements Completor
{
    private Parser $parser;

    /**
     * @var TolerantCompletor[]
     */
    private array $tolerantCompletors = [];

    /**
     * @param TolerantCompletor[] $tolerantCompletors
     */
    public function __construct(array $tolerantCompletors, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->tolerantCompletors = $tolerantCompletors;
    }

    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $truncatedSource = $this->truncateSource((string) $source, $byteOffset->toInt());

        $node = $this->parser->parseSourceFile($truncatedSource)->getDescendantNodeAtPosition(
            // the parser requires the byte offset, not the char offset
            strlen($truncatedSource)
        );

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

        // the parser will get very confused if there is an unterminated open quote,
        // if the very last non-whitepsace char is a quote, then add another to 
        // create a string literal.
        if ($lastChar === '\'' || $lastChar === '"') {
            $truncatedSource .= $lastChar;
        }

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
