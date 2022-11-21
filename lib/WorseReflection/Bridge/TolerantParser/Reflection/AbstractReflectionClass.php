<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TraitImport\TraitImports;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as PhpactorReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionTraitCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;

abstract class AbstractReflectionClass extends AbstractReflectedNode implements ReflectionClassLike
{
    abstract public function name(): ClassName;
    abstract public function docblock(): DocBlock;

    /**
     * @deprecated Use instanceof instead
     */
    public function isInterface(): bool
    {
        return $this instanceof ReflectionInterface;
    }

    /**
     * @deprecated Use instanceof instead
     */
    public function isTrait(): bool
    {
        return $this instanceof ReflectionTrait;
    }

    public function isEnum(): bool
    {
        return $this instanceof ReflectionEnum;
    }

    /**
     * @deprecated Use instanceof instead
     */
    public function isClass(): bool
    {
        return $this instanceof ReflectionClass;
    }

    public function isConcrete(): bool
    {
        return false;
    }

    public function deprecation(): Deprecation
    {
        return $this->docblock()->deprecation();
    }

    public function templateMap(): TemplateMap
    {
        return $this->docblock()->templateMap();
    }

    public function type(): ReflectedClassType
    {
        return TypeFactory::reflectedClass($this->serviceLocator()->reflector(), $this->name());
    }

    protected function resolveTraitMethods(
        TraitImports $traitImports,
        ReflectionClassLike $contextClass,
        ReflectionTraitCollection $traits
    ): PhpactorReflectionMethodCollection {
        $methods = ReflectionMethodCollection::empty();

        foreach ($traitImports as $traitImport) {
            try {
                $trait = $traits->get($traitImport->name());
            } catch (NotFound) {
                continue;
            }

            $traitMethods = [];
            foreach ($trait->methods($contextClass) as $method) {
                if (false === $traitImport->hasAliasFor($method->name())) {
                    $traitMethods[] = $method;
                    continue;
                }

                $traitAlias = $traitImport->getAlias($method->name());

                $virtualMethod = VirtualReflectionMethod::fromReflectionMethod($trait->methods()->get($traitAlias->originalName()))
                    ->withName($traitAlias->newName())
                    ->withVisibility($traitAlias->visiblity($method->visibility()));

                $traitMethods[] = $virtualMethod;
            }
            $methods = $methods->merge(ReflectionMethodCollection::fromReflectionMethods($traitMethods));
        }

        return $methods;
    }
}
