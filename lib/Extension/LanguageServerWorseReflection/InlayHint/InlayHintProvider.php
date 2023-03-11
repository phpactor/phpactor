<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

class InlayHintProvider
{
    public function __construct(private SourceCodeReflector $reflector)
    {
    }

    /**
     * @return iterable<InlayHint>
     */
    public function inlayHints(TextDocument $document): iterable
    {
        $walker = new InlayHintWalker();
        $this->reflector->walk($document, $walker);

        return $walker->hints();
    }
}
