<?php

namespace Phpactor\WorseReflection;

use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;

interface Reflector extends ClassReflector, SourceCodeReflector, FunctionReflector
{
}
