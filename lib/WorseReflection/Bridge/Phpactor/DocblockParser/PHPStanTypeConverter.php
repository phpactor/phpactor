<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFloatNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNullNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstFetchNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConditionalTypeForParameterNode;
use PHPStan\PhpDocParser\Ast\Type\ConstTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\ConditionalType;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\FloatLiteralType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\GlobbedConstantUnionType;
use Phpactor\WorseReflection\Core\Type\IntLiteralType;
use Phpactor\WorseReflection\Core\Type\IntMaxType;
use Phpactor\WorseReflection\Core\Type\IntNegative;
use Phpactor\WorseReflection\Core\Type\IntPositive;
use Phpactor\WorseReflection\Core\Type\IntRangeType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\InvokeableType;
use Phpactor\WorseReflection\Core\Type\ListType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NeverType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\ObjectType;
use Phpactor\WorseReflection\Core\Type\PseudoIterableType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\ResourceType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\ThisType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Type\VoidType;
use Phpactor\WorseReflection\Reflector;

class PHPStanTypeConverter
{
    public function __construct(private Reflector $reflector, private ?ReflectionScope $scope)
    {
    }

    public function convert(?TypeNode $type): Type
    {
        return match (true) {
            $type instanceof ArrayShapeNode => $this->convertArrayShape($type),
            $type instanceof ArrayTypeNode => new ArrayType($this->convert($type->type)),
            $type instanceof CallableTypeNode => $this->convertCallable($type),
            $type instanceof ConditionalTypeForParameterNode => $this->convertConditional($type),
            $type instanceof ConstTypeNode => $this->convertConstant($type),
            $type instanceof GenericTypeNode => $this->convertGeneric($type),
            $type instanceof IdentifierTypeNode => $this->convertSimpleIdentifier($type->name),
            $type instanceof IntersectionTypeNode => $this->convertIntersection($type),
            $type instanceof NullableTypeNode => new NullableType($this->convert($type->type)),
            // todo: ObjectShape
            $type instanceof ThisTypeNode => new ThisType(),
            $type instanceof UnionTypeNode => $this->convertUnion($type),
            default => new MissingType(),
        };
    }

    private function convertSimpleIdentifier(string $type): Type
    {
        return match ($type) {
            '' => new MissingType(),
            'array' => new ArrayType(new MissingType()),
            'bool', 'boolean' => new BooleanType(),
            'callable' => new CallableType([], new MissingType()),
            'class-string' => new ClassStringType(),
            'false' => new FalseType(),
            'float' => new FloatType(),
            'int', 'integer' => new IntType(),
            'iterable' => new PseudoIterableType(),
            'list' => new ListType(),
            'mixed' => new MixedType(),
            'negative-int' => new IntNegative(),
            'never' => new NeverType(),
            'null' => new NullType(),
            'object' => new ObjectType(),
            'positive-int' => new IntPositive(),
            'resource' => new ResourceType(),
            'self' => new SelfType(),
            'static' => new StaticType(),
            'string' => new StringType(),
            'void' => new VoidType(),
            default => $this->scope?->resolveFullyQualifiedName(new ReflectedClassType($this->reflector, ClassName::fromString($type))) ?? new ClassType(ClassName::fromString($type)),
        };
    }

    private function convertUnion(UnionTypeNode $union): Type
    {
        return new UnionType(...array_map(
            fn (TypeNode $node) => $this->convert($node),
            $union->types
        ));
    }

    private function convertIntersection(IntersectionTypeNode $type): Type
    {
        return new IntersectionType(...array_map(
            fn (TypeNode $node) => $this->convert($node),
            $type->types
        ));
    }

    private function convertGeneric(GenericTypeNode $type): Type
    {
        $internalType = $this->convert($type->type);
        if ($internalType instanceof ArrayType) {
            $parameters = $type->genericTypes;
            if (count($parameters) === 1) {
                return new ArrayType(
                    null,
                    $this->convert($parameters[0]),
                );
            }
            if (count($parameters) === 2) {
                return new ArrayType(
                    $this->convert($parameters[0]),
                    $this->convert($parameters[1]),
                );
            }
            return new ArrayType(new MissingType());
        }

        if ($internalType instanceof IntType) {
            $parameters = $type->genericTypes;
            if (count($parameters) === 2) {
                $start = $this->convert($parameters[0]);
                $end = $this->convert($parameters[1]);
                if ($start instanceof ClassType) {
                    if ($start->name()->short() === 'min') {
                        $start = null;
                    }
                }
                if ($end instanceof ClassType) {
                    if ($end->name()->short() === 'max') {
                        $end = null;
                    }
                }
                return new IntRangeType(
                    $start,
                    $end,
                );
            }
        }

        if ($internalType instanceof ClassStringType) {
            $parameters = $type->genericTypes;
            if (count($parameters) > 0) {
                return new ClassStringType(
                    ClassName::fromString($this->convert(
                        $parameters[0]
                    )->__toString())
                );
            }
            return $internalType;
        }

        if (!$internalType instanceof ClassType) {
            return new MissingType();
        }

        $parameters = $type->genericTypes;

        return new GenericClassType(
            $this->reflector,
            $internalType->name(),
            array_map(
                fn (TypeNode $node) => $this->convert($node),
                $parameters
            )
        );
    }

    /**
     * @return Type&InvokeableType
     */
    private function convertCallable(CallableTypeNode $callableNode): Type
    {
        $parameters = array_map(
            fn (CallableTypeParameterNode $type) => $this->convert($type->type),
            $callableNode->parameters
        );

        $type = $this->convert($callableNode->returnType);

        if (((string) $callableNode->identifier) === 'Closure') {
            return new ClosureType($this->reflector, $parameters, $type);
        }

        return new CallableType($parameters, $type);
    }

    private function convertArrayShape(ArrayShapeNode $type): ArrayShapeType
    {
        $typeMap = [];
        foreach ($type->items as $index => $keyValue) {
            $key = $keyValue->keyName ? ((string) $keyValue->keyName) : $index;
            $typeMap[$key] = $this->convert($keyValue->valueType);
        }

        return new ArrayShapeType($typeMap);
    }

    private function convertConstant(ConstTypeNode $type): Type
    {
        return match (true) {
            $type->constExpr instanceof ConstExprFalseNode => new FalseType(),
            $type->constExpr instanceof ConstExprFloatNode => new FloatLiteralType((float)$type->constExpr->value),
            $type->constExpr instanceof ConstExprIntegerNode => (int)$type->constExpr->value === PHP_INT_MAX ? new IntMaxType() : new IntLiteralType((int)$type->constExpr->value),
            $type->constExpr instanceof ConstExprNullNode => new NullType(),
            $type->constExpr instanceof ConstExprTrueNode => new BooleanLiteralType(true),
            $type->constExpr instanceof ConstFetchNode => new GlobbedConstantUnionType(new ClassType(ClassName::fromString($type->constExpr->className)), $type->constExpr->name),
            default => new StringLiteralType((string) $type->constExpr),
        };
    }

    private function convertConditional(ConditionalTypeForParameterNode $type): Type
    {
        return new ConditionalType(
            $type->parameterName,
            $this->convert($type->targetType),
            $this->convert($type->if),
            $this->convert($type->else)
        );
    }
}
