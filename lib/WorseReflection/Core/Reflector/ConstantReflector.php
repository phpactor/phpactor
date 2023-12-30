<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\Reflection\ReflectionDeclaredConstant;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\TextDocument;

interface ConstantReflector
{
    /**
     * @param string|Name $name
     */
    public function reflectConstant($name): ReflectionDeclaredConstant;

    /**
     * @param string|Name $name
     */
    public function sourceCodeForConstant($name): TextDocument;
}
