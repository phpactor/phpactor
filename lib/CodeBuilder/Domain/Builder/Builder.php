<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

interface Builder
{
    public function isModified(): bool;
    public function snapshot(): void;
    /**
     * @return list<string>
     */
    public static function childNames(): array;
}
