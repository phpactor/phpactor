<?php

namespace Phpactor\CodeTransform\Adapter\DocblockParser;

use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ParamTagPrototype;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ReturnTagPrototype;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\TagPrototype;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use RuntimeException;

class ParserDocblockUpdater implements DocBlockUpdater
{
    public function __construct(private DocblockParser $parser)
    {
    }

    public function set(string $docblockText, TagPrototype $prototype): string
    {
        $docblock = $this->parser->parse($docblockText);

        if (!$docblock instanceof Docblock) {
            return $docblockText;
        }

        return TextEdits::fromTextEdits($this->edits($docblock, $prototype))->apply($docblockText);
    }

    /**
     * @return array<int,TextEdit>
     */
    private function edits(Docblock $docblock, TagPrototype $prototype): array
    {
        if ($prototype instanceof ReturnTagPrototype) {
            return $this->updateTag(
                $docblock,
                $prototype,
                sprintf('@return %s', $prototype->type->__toString())
            );
        }

        if ($prototype instanceof ParamTagPrototype) {
            return $this->updateTag(
                $docblock,
                $prototype,
                sprintf('@param %s $%s', $prototype->type->__toString(), $prototype->name)
            );
        }

        throw new RuntimeException(sprintf(
            'Do not know how to update tag "%s"',
            get_class($prototype)
        ));
    }

    /**
     * @return array<int,TextEdit>
     */
    private function updateTag(Docblock $docblock, TagPrototype $prototype, string $tagText): array
    {
        // create
        if (strlen(trim($docblock->toString())) === 0) {
            return [
                TextEdit::create(
                    $docblock->start(),
                    0,
                    sprintf("\n\n    /**\n     * %s\n     */\n    ", $tagText),
                )
            ];
        }

        // update
        $edits = [];
        foreach ($docblock->tags() as $tag) {
            if ($prototype->matches($tag)) {
                $edits[] =
                    TextEdit::create(
                        $tag->start(),
                        $tag->length(),
                        $tagText . ' '
                    );
            }
        }

        if ($edits) {
            return $edits;
        }

        if ($line = $docblock->lastMultilineContentToken()) {
            return [
                TextEdit::create(
                    $line->end(),
                    0,
                    sprintf(
                        "* %s\n%s",
                        $tagText,
                        str_repeat(' ', $docblock->indentationLevel()),
                    ),
                )
            ];
        }

        if ($open = $docblock->phpDocOpen()) {
            if (!str_contains($docblock->toString(), "\n")) {
                return [
                    TextEdit::create(
                        $open->end(),
                        0,
                        sprintf(
                            ' %s',
                            $tagText
                        ),
                    )
                ];
            }
            return [
                TextEdit::create(
                    $open->end(),
                    0,
                    sprintf(
                        "\n%s* %s",
                        str_repeat(' ', $docblock->indentationLevel()),
                        $tagText
                    ),
                )
            ];
        }

        return [];
    }
}
