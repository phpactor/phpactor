<?php

namespace Phpactor\CodeTransform\Domain;

/**
 * @extends AbstractCollection<Generator>
 * @method string[] names()
 */
final class Generators extends AbstractCollection
{
    protected function type(): string
    {
        return Generator::class;
    }
}
