<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\TypeUtil;

class FunctionReturnTypeResolver
{
    public function __construct(private readonly ReflectionFunction $function)
    {
    }

    public function resolve(): Type
    {
        return TypeUtil::firstDefined(
            $this->getDocblockTypeFromFunction($this->function),
            $this->function->type()
        );
    }

    private function getDocblockTypeFromFunction(ReflectionFunction $function): Type
    {
        return $function->docblock()->returnType();
    }
}
