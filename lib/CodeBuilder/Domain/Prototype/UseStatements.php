<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

/**
 * @extends Collection<UseStatement>
 */
class UseStatements extends Collection
{
    /**
     * @param list<UseStatement> $useStatements
     */
    public static function fromUseStatements(array $useStatements): self
    {
        return new self($useStatements);
    }

    public function sorted(): UseStatements
    {
        $items = iterator_to_array($this);
        usort($items, function (UseStatement $left, UseStatement $right): int {
            return strcmp((string) $left, $right);
        });

        return new self($items);
    }

    protected function singularName(): string
    {
        return 'use statement';
    }
}
