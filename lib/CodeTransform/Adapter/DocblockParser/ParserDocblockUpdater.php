<?php

namespace Phpactor\CodeTransform\Adapter\DocblockParser;

use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type;

class ParserDocblockUpdater implements DocBlockUpdater
{
    public function __construct(private DocblockParser $parser)
    {
    }

    public function setReturnType(string $docblockText, Type $type): string
    {
        $docblock = $this->parser->parse($docblockText);

        if (!$docblock instanceof Docblock) {
            return $docblockText;
        }


        $edits = [];

        // update
        foreach ($docblock->descendantElements(ReturnTag::class) as $returnTag) {
            $edits[] = TextEdit::create(
                $returnTag->type()->start(),
                $returnTag->type()->length(),
                $type->__toString()
            );
        }

        // otherwise create
        if (count($edits) === 0) {
            $edits = $this->updateDocblock($docblock, $docblockText, $type);
        }

        return TextEdits::fromTextEdits($edits)->apply($docblockText);
    }

    /**
     * @return array<int,TextEdit>
     */
    private function updateDocblock(Docblock $docblock, string $docblockText, Type $type): array
    {
        if ($line = $docblock->lastMultilineContentToken()) {
            return [
                TextEdit::create(
                    $line->end(),
                    0,
                    sprintf(
                        "* @return %s\n%s",
                        $type->__toString(),
                        str_repeat(' ', $docblock->indentationLevel()),
                    ),
                )
            ];
        }

        if ($open = $docblock->phpDocOpen()) {
            if (!str_contains($docblockText, "\n")) {
                return [
                    TextEdit::create(
                        $open->end(),
                        0,
                        sprintf(
                            ' @return %s',
                            $type->__toString()
                        ),
                    )
                ];
            }
            return [
                TextEdit::create(
                    $open->end(),
                    0,
                    sprintf(
                        "\n%s* @return %s",
                        str_repeat(' ', $docblock->indentationLevel()),
                        $type->__toString()
                    ),
                )
            ];
        }

        return [];
    }
}
