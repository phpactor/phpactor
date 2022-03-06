<?php

namespace Phpactor\CodeTransform\Domain;

/**
 * @extends AbstractCollection<Transformer>
 */
final class Transformers extends AbstractCollection
{
    public function applyTo(SourceCode $code): SourceCode
    {
        foreach ($this as $transformer) {
            assert($transformer instanceof Transformer);
            $code = SourceCode::fromStringAndPath($transformer->transform($code)->apply($code), $code->path());
        }

        return $code;
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
