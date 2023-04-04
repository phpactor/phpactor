<?php

namespace Phpactor\CodeTransform\Domain;

use Amp\Promise;
use function Amp\call;

/**
 * @extends AbstractCollection<Transformer>
 */
final class Transformers extends AbstractCollection
{
    /**
     * @return Promise<SourceCode>
     */
    public function applyTo(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            foreach ($this as $transformer) {
                assert($transformer instanceof Transformer);
                $code = SourceCode::fromStringAndPath(
                    (yield $transformer->transform($code))->apply($code),
                    $code->uri()->__toString()
                );
            }

            return $code;
        });
    }

    public function in(array $transformerNames): self
    {
        $transformers = [];

        foreach ($transformerNames as $transformerName) {
            $transformers[] = $this->get($transformerName);
        }

        return new self($transformers);
    }

    protected function type(): string
    {
        return Transformer::class;
    }
}
