<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase as CoreReflectionEnumCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;

class ReflectionEnumCase extends AbstractReflectionClassMember implements CoreReflectionEnumCase
{
    private ServiceLocator $serviceLocator;
    
    private EnumCaseDeclaration $node;
    
    private ReflectionEnum $enum;

    public function __construct(
        ServiceLocator $serviceLocator,
        ReflectionEnum $class,
        EnumCaseDeclaration $node
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->enum = $class;
    }

    public function name(): string
    {
        $name = $this->node->name;
        /** @phpstan-ignore-next-line Invalid type hint in TP */
        if ($name instanceof Token) {
            return $name->getText($this->node->getFileContents());
        }
        return $this->node->name->__toString();
    }

    public function type(): Type
    {
        return TypeFactory::unknown();
    }

    public function class(): ReflectionClassLike
    {
        return $this->enum;
    }

    public function inferredTypes(): Types
    {
        if (TypeFactory::unknown() !== $this->type()) {
            return Types::fromTypes([ $this->type() ]);
        }

        return Types::empty();
    }

    public function isVirtual(): bool
    {
        return false;
    }
    
    /**
     * @return mixed
     */
    public function value()
    {
        if ($this->node->assignment === null) {
            return null;
        }

        return $this->serviceLocator()
                    ->symbolContextResolver()
                    ->resolveNode(
                        new Frame('_'),
                        $this->node->assignment
                    )->value();
    }

    public function memberType(): string
    {
        return ReflectionMember::TYPE_ENUM;
    }

    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
