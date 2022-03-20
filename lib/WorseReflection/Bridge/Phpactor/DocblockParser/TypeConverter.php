<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\WorseReflection\Core\Type\ArrayKeyType;
use Phpactor\WorseReflection\DocblockParser\Ast\Node;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ArrayNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\CallableNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ClassNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\GenericNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ListNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\NullNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ScalarNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\ThisNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Type\UnionNode;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\IterablePrimitiveType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\ObjectType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\ResourceType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Type\VoidType;
use Phpactor\WorseReflection\Reflector;

class TypeConverter
{
    private Reflector $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function convert(?TypeNode $type, ?ReflectionScope $scope = null): Type
    {
        if ($type instanceof ScalarNode) {
            return $this->convertScalar($type->toString());
        }
        if ($type instanceof ListNode) {
            return $this->convertList($type, $scope);
        }
        if ($type instanceof ArrayNode) {
            return $this->convertArray($type);
        }
        if ($type instanceof UnionNode) {
            return $this->convertUnion($type);
        }
        if ($type instanceof GenericNode) {
            return $this->convertGeneric($type, $scope);
        }
        if ($type instanceof ClassNode) {
            return $this->convertClass($type, $scope);
        }
        if ($type instanceof ThisNode) {
            return $this->convertThis($type);
        }
        if ($type instanceof NullNode) {
            return new NullType();
        }

        if ($type instanceof CallableNode) {
            return $this->convertCallable($type, $scope);
        }

        return new MissingType();
    }

    private function convertScalar(string $type): Type
    {
        if ($type === 'int') {
            return new IntType();
        }
        if ($type === 'string') {
            return new StringType();
        }
        if ($type === 'float') {
            return new FloatType();
        }
        if ($type === 'mixed') {
            return new MixedType();
        }
        if ($type === 'bool') {
            return new BooleanType();
        }
        if ($type === 'callable') {
            return new CallableType([], new MissingType());
        }

        return new MissingType();
    }

    private function convertArray(ArrayNode $type): Type
    {
        return new ArrayType(new ArrayKeyType(), new MissingType());
    }

    private function convertUnion(UnionNode $union): Type
    {
        return new UnionType(...array_map(
            fn (Node $node) => $this->convert($node),
            iterator_to_array($union->types->types())
        ));
    }

    private function convertGeneric(GenericNode $type, ?ReflectionScope $scope): Type
    {
        if ($type->type instanceof ArrayNode) {
            $parameters = array_values(iterator_to_array($type->parameters()->types()));
            if (count($parameters) === 1) {
                return new ArrayType(
                    new ArrayKeyType(),
                    $this->convert($parameters[0], $scope)
                );
            }
            if (count($parameters) === 2) {
                return new ArrayType(
                    $this->convert($parameters[0], $scope),
                    $this->convert($parameters[1], $scope),
                );
            }
            return new MissingType();
        }

        $classType = $this->convert($type->type, $scope);

        if (!$classType instanceof ClassType) {
            return new MissingType();
        }

        $parameters = iterator_to_array($type->parameters()->types());

        if (count($parameters) === 1) {
            // pretend this is a traversable
            return TypeFactory::collection($this->reflector, $classType, $this->convert($parameters[0]));
        }

        return new GenericClassType(
            $this->reflector,
            $classType->name(),
            new TemplateMap(array_map(
                fn (TypeNode $node) => $this->convert($node, $scope),
                $parameters
            ))
        );
    }

    private function convertClass(ClassNode $typeNode, ?ReflectionScope $scope): Type
    {
        $name = $typeNode->name()->toString();

        if ($name === 'static') {
            return new StaticType();
        }

        if ($name === 'iterable') {
            return new IterablePrimitiveType();
        }

        if ($name === 'self') {
            return new SelfType();
        }

        if ($name === 'object') {
            return new ObjectType();
        }

        if ($name === 'resource') {
            return new ResourceType();
        }

        if ($name === 'void') {
            return new VoidType();
        }

        $type = new ReflectedClassType(
            $this->reflector,
            ClassName::fromString(
                $typeNode->name()->toString()
            )
        );

        if ($scope) {
            return $scope->resolveFullyQualifiedName($type);
        }

        return $type;
    }

    private function convertList(ListNode $type, ?ReflectionScope $scope): Type
    {
        return new ArrayType($this->convert($type->type, $scope));
    }

    private function convertThis(ThisNode $type): Type
    {
        return new SelfType();
    }

    private function convertCallable(CallableNode $type, ?ReflectionScope $scope): CallableType
    {
        return new CallableType(array_map(function (TypeNode $type) {
            return $this->convert($type);
        }, $type->parameters ? iterator_to_array($type->parameters->types()) : []), $this->convert($type->type));
    }
}
