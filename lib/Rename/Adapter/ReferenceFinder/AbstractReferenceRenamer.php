<?php

namespace Phpactor\Rename\Adapter\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Rename\Model\Exception\CouldNotRename;
use Phpactor\Rename\Model\LocatedTextEdit;
use Phpactor\Rename\Model\Renamer;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\LocationRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextEdit as PhpactorTextEdit;

abstract class AbstractReferenceRenamer implements Renamer
{
    public function __construct(
        private ReferenceFinder $referenceFinder,
        private TextDocumentLocator $locator,
        private Parser $parser
    ) {
    }

    public function getRenameRange(TextDocument $textDocument, ByteOffset $offset): ?ByteOffsetRange
    {
        $node = $this->parser->parseSourceFile($textDocument->__toString())->getDescendantNodeAtPosition($offset->toInt());
        return $this->getRenameRangeForNode($node);
    }

    public function rename(TextDocument $textDocument, ByteOffset $offset, string $newName): Generator
    {
        $range = $this->getRenameRange($textDocument, $offset);
        if (null === $range) {
            return;
        }
        $originalName = $this->rangeText($textDocument, $range);
        yield from $this->doRename($textDocument, $offset, $range, $originalName, $newName);
    }

    /**
     * @return Generator<LocatedTextEdit>
     */
    protected function doRename(TextDocument $textDocument, ByteOffset $offset, ByteOffsetRange $range, string $originalName, string $newName): Generator
    {
        foreach ($this->referenceFinder->findReferences($textDocument, $offset) as $reference) {
            if (!$reference->isSurely()) {
                continue;
            }

            yield $this->renameEdit($reference->range(), $range, $originalName, $newName);
        }
    }

    abstract protected function getRenameRangeForNode(Node $node): ?ByteOffsetRange;

    /**
     * @param Token|Node $tokenOrNode
     */
    protected function offsetRangeFromToken($tokenOrNode, bool $hasDollar): ?ByteOffsetRange
    {
        if (!$tokenOrNode instanceof Token) {
            return null;
        }

        if ($hasDollar) {
            return ByteOffsetRange::fromInts($tokenOrNode->start + 1, $tokenOrNode->getEndPosition());
        }

        return ByteOffsetRange::fromInts($tokenOrNode->start, $tokenOrNode->getEndPosition());
    }

    protected function renameEdit(LocationRange $location, ?ByteOffsetRange $range, string $originalName, string $newName): LocatedTextEdit
    {
        $referenceDocument = $this->locator->get($location->uri());

        // We could probably use the complete range here instead of only using the location
        $range = $this->getRenameRange($referenceDocument, $location->range()->start());

        if (null === $range) {
            throw new CouldNotRename(sprintf(
                'Could not find corresponding reference to member name "%s" in document "%s" at offset %s',
                $originalName,
                $referenceDocument->uri()->__toString(),
                $location->range()->start()->toInt()
            ));
        }

        $foundName = $this->rangeText($referenceDocument, $range);
        if ($foundName !== $originalName) {
            throw new CouldNotRename(sprintf(
                'Found referenced name "%s" in "%s" does not match original name "%s", perhaps the text document is out of sync?',
                $foundName,
                $referenceDocument->uri()->__toString(),
                $originalName
            ));
        }

        return new LocatedTextEdit(
            $location->uri(),
            PhpactorTextEdit::create(
                $range->start(),
                $range->end()->toInt() - $range->start()->toInt(),
                $newName
            )
        );
    }

    private function rangeText(TextDocument $textDocument, ByteOffsetRange $range): string
    {
        return substr(
            $textDocument->__toString(),
            $range->start()->toInt(),
            $range->end()->toInt() - $range->start()->toInt()
        );
    }
}
