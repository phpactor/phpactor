<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

interface NamedBuilder extends Builder
{
    public function builderName(): string;
}
