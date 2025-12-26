<?php

namespace Phpactor\CodeTransform\Adapter\DocblockParser;

use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ExtendsTagPrototype;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ImplementsTagPrototype;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ParamTagPrototype;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ReturnTagPrototype;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\TagPrototype;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use RuntimeException;

/**
 * TODO: Incorporate this into the "code builder"
 */
class ParserDocblockUpdater implements DocBlockUpdater
{
    public function __construct(
        private readonly DocblockParser $parser,
        private readonly TextFormat $textFormat
    ) {
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

        if ($prototype instanceof ExtendsTagPrototype) {
            return $this->updateTag(
                $docblock,
                $prototype,
                sprintf('@extends %s', $prototype->type->short()),
                0
            );
        }

        if ($prototype instanceof ImplementsTagPrototype) {
            return $this->updateTag(
                $docblock,
                $prototype,
                sprintf('@implements %s', $prototype->type->short()),
                0
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
    private function updateTag(Docblock $docblock, TagPrototype $prototype, string $tagText, int $indent = 1): array
    {
        // create
        if (strlen(trim($docblock->toString())) === 0) {
            $indent = $this->textFormat->indent('', $indent);
            return [
                TextEdit::create(
                    $docblock->start(),
                    0,
                    sprintf(
                        "\n%s/**\n%s * %s\n%s */\n%s",
                        $indent,
                        $indent,
                        $tagText,
                        $indent,
                        $indent,
                    ),
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
                        $prototype->endOffsetFor($tag) - $tag->start(),
                        $tagText
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
