<?php

namespace Phpactor\CodeBuilder\Domain\Builder\Exception;

use OutOfBoundsException;
use Phpactor\CodeBuilder\Domain\Builder\Builder;

class InvalidBuilderException extends OutOfBoundsException
{
    public function __construct(Builder $builder, Builder $containerBuilder)
    {
        parent::__construct(sprintf(
            'Builder "%s" cannot be added to builder "%s"',
            get_class($builder),
            get_class($containerBuilder)
        ));
    }
}
