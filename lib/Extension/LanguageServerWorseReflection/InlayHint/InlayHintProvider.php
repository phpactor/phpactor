<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class InlayHintProvider
{
    public function __construct(private SourceCodeReflector $reflector)
    {
    }

    /**
     * @return list<InlayHint>
     */
    public function inlayHints(TextDocument $document, ByteOffsetRange $range): array
    {
        $walker = new InlayHintWalker($range);
        $this->reflector->walk($document, $walker);

        return $walker->hints();
    }
}
