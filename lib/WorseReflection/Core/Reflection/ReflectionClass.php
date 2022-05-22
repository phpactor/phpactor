<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\OldCollection\ReflectionTraitCollection;

interface ReflectionClass extends ReflectionClassLike
{
    public function isAbstract(): bool;

    public function constants(): ReflectionConstantCollection;

    public function parent(): ?ReflectionClass;

    public function ancestors(): ReflectionClassCollection;

    public function properties(ReflectionClassLike $contextClass = null): ReflectionPropertyCollection;

    /**
     * @return ReflectionInterfaceCollection<ReflectionInterface>
     */
    public function interfaces(): ReflectionInterfaceCollection;

    public function traits(): ReflectionTraitCollection;

    public function memberListPosition(): Position;

    public function isFinal();
}
