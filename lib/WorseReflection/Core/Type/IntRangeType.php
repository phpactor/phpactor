<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

final class IntRangeType extends IntType
{
    public function __construct(public ?Type $lower, public ?Type $upper)
    {
    }

    public function __toString(): string
    {
        return sprintf('int<%s, %s>', $this->lower ?? 'min', $this->upper ?? 'max');
    }
}
