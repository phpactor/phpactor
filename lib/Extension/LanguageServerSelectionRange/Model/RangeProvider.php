<?php

namespace Phpactor\Extension\LanguageServerSelectionRange\Model;

use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\SelectionRange;
use Phpactor\TextDocument\ByteOffset;

class RangeProvider
{
    public function __construct(private readonly AstProvider $parser)
    {
    }

    /**
     * @param array<ByteOffset> $offsets
     *
     * @return array<SelectionRange>
     */
    public function provide(TextDocument $source, array $offsets): array
    {
        $rootNode = $this->parser->get($source);

        $selectionRanges = [];
        foreach ($offsets as $byteOffset) {
            $node = $rootNode->getDescendantNodeAtPosition($byteOffset->toInt());
            $range = $this->buildRange($node, $source);
            if ($range->parent) {
                $range->parent = $this->buildRange($node->parent, $source);
            }
            $selectionRanges[] = $range;
        }

        return $selectionRanges;
    }

    private function buildRange(Node $node, string $source): SelectionRange
    {
        return new SelectionRange(
            new Range(
                PositionConverter::intByteOffsetToPosition(
                    $node->getStartPosition(),
                    $source
                ),
                PositionConverter::intByteOffsetToPosition(
                    $node->getEndPosition(),
                    $source
                )
            )
        );
    }
}
