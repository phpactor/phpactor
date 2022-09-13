<?php

namespace Phpactor\Search\Model;

class Matches
{
    public function __construct(array $array)
    {
    }

    public static function none(): self
    {
        return new self([]);
    }
}
