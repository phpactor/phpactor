<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class GenericClassType extends ReflectedClassType implements IterableType
{
    /**
     * @var GenericClassType[]
     */
    private array $extendAndImplements;

    private TemplateMap $templateMap;

    /**
     * @param GenericClassType[] $extendAndImplements
     */
    public function __construct(ClassReflector $reflector, ClassName $name, TemplateMap $templateMap, array $extendAndImplements = [])
    {
        parent::__construct($reflector, $name);
        $this->extendAndImplements = $extendAndImplements;
        $this->templateMap = $templateMap;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s<%s>',
            $this->name->__toString(),
            implode(',', array_map(fn (Type $t) => $t->__toString(), $this->templateMap->toArray()))
        );
    }

    public function templateMap(): TemplateMap
    {
        return $this->templateMap;
    }

    public function iterableValueType(): Type
    {
        if ($this->accepts(TypeFactory::class('Traversable'))->isTrue()) {
            return $this->templateMap->get('TValue');
        }

        return new MissingType();
    }

    public function toPhpString(): string
    {
        return $this->name->__toString();
    }

    public function accepts(Type $type): Trinary
    {
        if ($this->is($type)->isTrue()) {
            return Trinary::true();
        }

        foreach ($this->extendAndImplements as $generic) {
            if ($generic->accepts($type)->isTrue()) {
                return Trinary::true();
            }
        }

        return Trinary::false();
    }
}
