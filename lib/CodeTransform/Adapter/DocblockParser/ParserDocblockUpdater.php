<?php

namespace Phpactor\CodeTransform\Adapter\DocblockParser;

use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Ast\Tag\ParamTag;
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
            $edits = $this->updateReturnType($docblock, $docblockText, $type);
        }

        return TextEdits::fromTextEdits($edits)->apply($docblockText);
    }

    public function setParam(string $docblockText, string $paramName, Type $paramType): string
    {
        $docblock = $this->parser->parse($docblockText);

        if (!$docblock instanceof Docblock) {
            return $docblockText;
        }

        $edits = [];

        $edits = $this->updateParam($docblock, $docblockText, $paramName, $paramType);

        return TextEdits::fromTextEdits($edits)->apply($docblockText);
    }

    /**
     * @return array<int,TextEdit>
     */
    private function updateReturnType(Docblock $docblock, string $docblockText, Type $type): array
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

    /**
     * @return array<int,TextEdit>
     */
    private function updateParam(Docblock $docblock, string $docblockText, string $paramName, Type $paramType): array
    {
        if ($line = $docblock->lastMultilineContentToken()) {
            return [
                TextEdit::create(
                    $line->end(),
                    0,
                    sprintf(
                        "* @param %s $%s\n%s",
                        $paramType->__toString(),
                        $paramName,
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
                            ' @param %s $%s',
                            $paramType->__toString(),
                            $paramName,
                        ),
                    )
                ];
            }
            return [
                TextEdit::create(
                    $open->end(),
                    0,
                    sprintf(
                        "\n%s* @param %s $%s",
                        str_repeat(' ', $docblock->indentationLevel()),
                        $paramType->__toString(),
                        $paramName,
                    ),
                )
            ];
        }

        return [];
    }
}
