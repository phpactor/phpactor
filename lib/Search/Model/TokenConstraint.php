<?php

namespace Phpactor\Search\Model;

interface TokenConstraint
{
    public function placeholder(): string;

    public function describe(): string;
}
