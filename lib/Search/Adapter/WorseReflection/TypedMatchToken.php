<?php

namespace Phpactor\Search\Adapter\WorseReflection;

use Phpactor\Search\Model\MatchToken;
use Phpactor\WorseReflection\Core\Type;

class TypedMatchToken
{
    public string $name;
    public MatchToken $token;
    public Type $type;

    public function __construct(string $name, MatchToken $token, Type $type)
    {
        $this->name = $name;
        $this->token = $token;
        $this->type = $type;
    }
}
