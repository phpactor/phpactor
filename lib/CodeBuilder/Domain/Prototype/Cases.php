<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<Case>
 */
class Cases extends Collection
{
    /**
     * @param Case[] $methods
     */
    public static function fromCases(array $cases): self
    {
        return new self(array_reduce($cases, function ($acc, $case) {
            $acc[$case->name()] = $case;
            return $acc;
        }, []));
    }

    protected function singularName(): string
    {
        return 'case';
    }
}
