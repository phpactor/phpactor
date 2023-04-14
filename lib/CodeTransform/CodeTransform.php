<?php

namespace Phpactor\CodeTransform;

use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformers;
use function Amp\Promise\wait;

class CodeTransform
{
    private function __construct(private Transformers $transformers)
    {
    }

    public static function fromTransformers(Transformers $transformers): CodeTransform
    {
        return new self($transformers);
    }

    public function transformers(): Transformers
    {
        return $this->transformers;
    }

    /**
     * @param mixed $code
     */
    public function transform($code, array $transformations): SourceCode
    {
        $code = SourceCode::fromUnknown($code);
        $transformers = $this->transformers->in($transformations);

        return wait($transformers->applyTo($code));
    }
}
