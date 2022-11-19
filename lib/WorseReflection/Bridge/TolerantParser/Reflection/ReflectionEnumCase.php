<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnumCase as CoreReflectionEnumCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use RuntimeException;

class ReflectionEnumCase extends AbstractReflectionClassMember implements CoreReflectionEnumCase
{
    public function __construct(
        private ServiceLocator $serviceLocator,
        private ReflectionEnum $enum,
        private EnumCaseDeclaration $node
    ) {
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

    public function nameRange(): ByteOffsetRange
    {
        $name = $this->node->name;
        return ByteOffsetRange::fromInts($name->getStartPosition(), $name->getEndPosition());
    }

    public function type(): Type
    {
        if ($this->class()->isBacked()) {
            return TypeFactory::enumBackedCaseType($this->class()->type(), $this->name(), $this->value());
        }
        return TypeFactory::enumCaseType($this->serviceLocator()->reflector(), $this->class()->type(), $this->name());
    }

    /**
     * @return ReflectionEnum
     */
    public function class(): ReflectionClassLike
    {
        return $this->enum;
    }

    public function inferredType(): Type
    {
        if (TypeFactory::unknown() !== $this->type()) {
            return $this->type();
        }

        return TypeFactory::undefined();
    }

    public function isVirtual(): bool
    {
        return false;
    }


    public function value(): Type
    {
        if ($this->node->assignment === null) {
            return new MissingType();
        }

        return $this->serviceLocator()
                    ->symbolContextResolver()
                    ->resolveNode(
                        new Frame('_'),
                        $this->node->assignment
                    )->type();
    }

    public function memberType(): string
    {
        return ReflectionMember::TYPE_ENUM;
    }

    public function withClass(ReflectionClassLike $class): ReflectionMember
    {
        if (!$class instanceof ReflectionEnum) {
            throw new RuntimeException(
                'Cannot make case member part of a non-enum reflection'
            );
        }

        return new self($this->serviceLocator, $class, $this->node);
    }

    public function isStatic(): bool
    {
        return true;
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
