<?php

namespace Phpactor\WorseReflection\Core\Reflection\Collection;

use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass as PhpactorReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum as PhpactorReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface as PhpactorReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait as PhpactorReflectionTrait;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\SourceCode;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionClassDecorator;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionInterfaceDecorator;

/**
 * @extends AbstractReflectionCollection<PhpactorReflectionClass>
 */
final class ReflectionClassCollection extends AbstractReflectionCollection
{
    public function concrete(): self
    {
        return new static(array_filter($this->items, function ($item) {
            return $item->isConcrete();
        }));
    }

    protected function collectionType(): string
    {
        return ReflectionClassCollection::class;
    }
}
