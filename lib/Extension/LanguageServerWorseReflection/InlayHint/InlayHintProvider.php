<?php

namespace Phpactor\Extension\LanguageServerWorseReflection\InlayHint;

use Amp\CancellationTokenSource;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\InlayHint;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use function Amp\call;
use function Amp\delay;

class InlayHintProvider
{
    private ?CancellationTokenSource $previousCancellationSource = null;

    public function __construct(private SourceCodeReflector $reflector, private InlayHintOptions $options)
    {
    }

    /**
     * @return Promise<list<InlayHint>>
     */
    public function inlayHints(TextDocument $document, ByteOffsetRange $range): Promise
    {
        // ensure we only process one inlay request at a time
        if ($this->previousCancellationSource) {
            $this->previousCancellationSource->cancel();
        }
        $cancellationSource = new CancellationTokenSource();
        $this->previousCancellationSource = $cancellationSource;
        $cancellation = $cancellationSource->getToken();

        return call(function () use ($document, $range, $cancellation) {
            $walker = new InlayHintWalker($range, $this->options);
            foreach ($this->reflector->walk($document, $walker) as $tick) {
                yield delay(0);
                if ($cancellation->isRequested()) {
                    return $walker->hints();
                }
            }

            return $walker->hints();
        });
    }
}
