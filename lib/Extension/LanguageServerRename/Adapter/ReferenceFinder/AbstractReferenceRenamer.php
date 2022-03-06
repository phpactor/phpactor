<?php

namespace Phpactor\Extension\LanguageServerRename\Adapter\ReferenceFinder;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Extension\LanguageServerRename\Model\Exception\CouldNotRename;
use Phpactor\Extension\LanguageServerRename\Model\LocatedTextEdit;
use Phpactor\Extension\LanguageServerRename\Model\Renamer;
use Phpactor\ReferenceFinder\ReferenceFinder;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextEdit as PhpactorTextEdit;

abstract class AbstractReferenceRenamer implements Renamer
{
    /**
     * @var ReferenceFinder
     */
    private $referenceFinder;

    /**
     * @var TextDocumentLocator
     */
    private $locator;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(
        ReferenceFinder $referenceFinder,
        TextDocumentLocator $locator,
        Parser $parser
    ) {
        $this->referenceFinder = $referenceFinder;
        $this->locator = $locator;
        $this->parser = $parser;
    }

    public function getRenameRange(TextDocument $textDocument, ByteOffset $offset): ?ByteOffsetRange
    {
        $node = $this->parser->parseSourceFile($textDocument->__toString())->getDescendantNodeAtPosition($offset->toInt());
        return $this->getRenameRangeForNode($node);
    }

    /**
     * {@inheritDoc}
     */
    public function rename(TextDocument $textDocument, ByteOffset $offset, string $newName): Generator
    {
        $range = $this->getRenameRange($textDocument, $offset);
        $originalName = $this->rangeText($textDocument, $range);

        foreach ($this->referenceFinder->findReferences($textDocument, $offset) as $reference) {
            if (!$reference->isSurely()) {
                continue;
            }

            $referenceDocument = $this->locator->get($reference->location()->uri());

            $range = $this->getRenameRange($referenceDocument, $reference->location()->offset());

            if (null === $range) {
                throw new CouldNotRename(sprintf(
                    'Could not find corresponding reference to member name "%s" in document "%s" at offset %s',
                    $originalName,
                    $referenceDocument->uri()->__toString(),
                    $reference->location()->offset()->toInt()
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

            yield new LocatedTextEdit(
                $reference->location()->uri(),
                PhpactorTextEdit::create(
                    $range->start(),
                    $range->end()->toInt() - $range->start()->toInt(),
                    $newName
                )
            );
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

    private function rangeText(TextDocument $textDocument, ByteOffsetRange $range): string
    {
        return substr(
            $textDocument->__toString(),
            $range->start()->toInt(),
            $range->end()->toInt() - $range->start()->toInt()
        );
    }
}
