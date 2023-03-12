<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use function Amp\call;
use function Amp\delay;

class InlayHintProvider
{
    public function __construct(private SourceCodeReflector $reflector, private InlayHintOptions $options)
    {
    }

    /**
     * @return Promise<list<InlayHint>>
     */
    public function inlayHints(TextDocument $document, ByteOffsetRange $range): Promise
    {
        return call(function () use ($document, $range) {
            $walker = new InlayHintWalker($range, $this->options);
            foreach ($this->reflector->walk($document, $walker) as $tick) {
                yield delay(0);
            }

            return $walker->hints();
        });
    }
}
