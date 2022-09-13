<?php

namespace Phpactor\Search\Model;

class Matcher
{
    public function match(string $pattern): Matches
    {
        return Matches::none();
    }
}
