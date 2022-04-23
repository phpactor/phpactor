<?php

namespace Phpactor\CodeTransform\Domain;

/**
 * @method static Diagnostics fromArray(array $diagnostics)
 * @extends AbstractCollection<Diagnostic>
 */
class Diagnostics extends AbstractCollection
{
    public static function none(): self
    {
        return new self([]);
    }

    protected function type(): string
    {
        return Diagnostic::class;
    }
}
