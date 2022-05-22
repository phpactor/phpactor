<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionInterfaceCollection;

interface ReflectionInterface extends ReflectionClassLike
{
    public function constants(): ReflectionConstantCollection;

    public function parents(): ReflectionInterfaceCollection;

    public function isInstanceOf(ClassName $className): bool;
}
