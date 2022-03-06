<?php

namespace Phpactor\Extension\LanguageServerRename\Model\Renamer;

use Generator;
use Phpactor\Extension\LanguageServerRename\Model\Renamer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;

class ChainRenamer implements Renamer
{
    /** @var Renamer[] */
    private $renamers;

    public function __construct(array $renamers)
    {
        $this->renamers = $renamers;
    }

    public function getRenameRange(TextDocument $textDocument, ByteOffset $offset): ?ByteOffsetRange
    {
        foreach ($this->renamers as $renamer) {
            if (null !== ($range = $renamer->getRenameRange($textDocument, $offset))) {
                return $range;
            }
        }
        return null;
    }
    /**
     * {@inheritDoc}
     */
    public function rename(TextDocument $textDocument, ByteOffset $offset, string $newName): Generator
    {
        foreach ($this->renamers as $renamer) {
            if (null !== ($range = $renamer->getRenameRange($textDocument, $offset))) {
                yield from $renamer->rename($textDocument, $offset, $newName);
                return;
            }
        }
    }
}
