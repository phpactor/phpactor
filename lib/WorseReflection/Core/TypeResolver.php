<?php

namespace Phpactor\WorseReflection\Core;

interface TypeResolver
{
    public function resolve(Type $type): Type;
}
