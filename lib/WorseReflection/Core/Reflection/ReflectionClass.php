<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;

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

    public function memberListPosition(): ByteOffsetRange;

    public function isFinal(): bool;
}
