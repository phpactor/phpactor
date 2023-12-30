<?php

declare(strict_types=1);

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;
use Phpactor\CodeBuilder\Domain\Prototype\Case_;

class CaseBuilder extends AbstractBuilder implements NamedBuilder
{
    public function __construct(
        protected EnumBuilder $enumBuilder,
        protected string $name
    ) {
    }

    public function name(string $name): void
    {
        $this->name = $name;
    }

    public function end(): EnumBuilder
    {
        return $this->enumBuilder;
    }

    public static function childNames(): array
    {
        return ['name'];
    }

    public function builderName(): string
    {
        return $this->name;
    }

    public function build(): Case_
    {
        return new Case_($this->name, null, UpdatePolicy::fromModifiedState($this->isModified()));
    }
}
