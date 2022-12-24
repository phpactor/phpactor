<?php

namespace Phpactor\CodeTransform\Adapter\DocblockParser;

use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\DocblockParser\Parser;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Type;

class ParserDocblockUpdater implements DocBlockUpdater
{
    public function __construct(private DocblockParser $parser) {
    }

    public function setReturnType(string $docblockText, Type $type): string
    {
        $docblock = $this->parser->parse($docblockText);

        if (!$docblock instanceof Docblock) {
            return $docblockText;
        }

        $edits = [];
        foreach ($docblock->descendantElements(ReturnTag::class) as $returnTag) {
            $edits[] = TextEdit::create(
                $returnTag->type()->start(),
                $returnTag->type()->length(),
                $type->__toString()
            );
        }


        if (count($edits) === 0) {
            if ($open = $docblock->phpDocOpen()) {
                $edits[] = TextEdit::create(
                    $open->end(),
                    0,
                    sprintf(' @return %s', $type->__toString()),
                );

            }
        }

        return TextEdits::fromTextEdits($edits)->apply($docblockText);
    }
}
