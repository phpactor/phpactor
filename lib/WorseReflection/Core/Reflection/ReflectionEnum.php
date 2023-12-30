<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionEnumCaseCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionEnum extends ReflectionClassLike
{
    public function docblock(): DocBlock;

    public function properties(): ReflectionPropertyCollection;

    public function cases(): ReflectionEnumCaseCollection;

    public function isBacked(): bool;

    public function backedType(): Type;

    public function traits(): ReflectionTraitCollection;
}
