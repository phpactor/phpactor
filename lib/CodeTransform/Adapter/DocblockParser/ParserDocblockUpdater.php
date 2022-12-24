<?php

namespace Phpactor\CodeTransform\Adapter\DocblockParser;

use Phpactor\CodeTransform\Domain\DocBlockUpdater;
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
        $edits = [];
        foreach ($docblock->descendantElements(ReturnTag::class) as $returnTag) {
            $edits[] = TextEdit::create(
                $returnTag->type()->start(),
                $returnTag->type()->length(),
                $type->__toString()
            );
        }

        return TextEdits::fromTextEdits($edits)->apply($docblockText);
    }
}
