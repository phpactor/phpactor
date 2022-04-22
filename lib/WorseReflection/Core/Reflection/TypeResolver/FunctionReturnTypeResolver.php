<?php

namespace Phpactor\WorseReflection\Core\Reflection\TypeResolver;

use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\TypeUtil;

class FunctionReturnTypeResolver
{
    private ReflectionFunction $function;

    public function __construct(ReflectionFunction $function)
    {
        $this->function = $function;
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
