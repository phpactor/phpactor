<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\ConditionalNode;
use Phpactor\DocblockParser\Ast\Type\ArrayShapeNode;
use Phpactor\DocblockParser\Ast\Type\IntersectionNode;
use Phpactor\DocblockParser\Ast\Type\ListNode;
use Phpactor\DocblockParser\Ast\Type\LiteralFloatNode;
use Phpactor\DocblockParser\Ast\Type\LiteralIntegerNode;
use Phpactor\DocblockParser\Ast\Type\LiteralStringNode;
use Phpactor\DocblockParser\Ast\Type\NullableNode;
use Phpactor\DocblockParser\Ast\Type\ConstantNode;
use Phpactor\DocblockParser\Ast\Type\ParenthesizedType;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type\ArrayKeyType;
use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Type\ArrayNode;
use Phpactor\DocblockParser\Ast\Type\CallableNode;
use Phpactor\DocblockParser\Ast\Type\ClassNode;
use Phpactor\DocblockParser\Ast\Type\GenericNode;
use Phpactor\DocblockParser\Ast\Type\ListBracketsNode;
use Phpactor\DocblockParser\Ast\Type\NullNode;
use Phpactor\DocblockParser\Ast\Type\ScalarNode;
use Phpactor\DocblockParser\Ast\Type\ThisNode;
use Phpactor\DocblockParser\Ast\Type\UnionNode;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
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
use Phpactor\WorseReflection\Core\Type\ParenthesizedType as PhpactorParenthesizedType;
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

class TypeConverter
{
    public function __construct(private Reflector $reflector, private ?ReflectionScope $scope)
    {
    }

    public function convert(?TypeNode $type): Type
    {
        if ($type instanceof ScalarNode) {
            return $this->convertScalar($type->toString());
        }
        if ($type instanceof ListNode) {
            return $this->convertList($type);
        }
        if ($type instanceof ListBracketsNode) {
            return $this->convertListBrackets($type);
        }
        if ($type instanceof ArrayNode) {
            return $this->convertArray($type);
        }
        if ($type instanceof ArrayShapeNode) {
            return $this->convertArrayShape($type);
        }
        if ($type instanceof UnionNode) {
            return $this->convertUnion($type);
        }
        if ($type instanceof IntersectionNode) {
            return $this->convertIntersection($type);
        }
        if ($type instanceof GenericNode) {
            $node = $this->convertGeneric($type);
            return $node;
        }
        if ($type instanceof ClassNode) {
            return $this->convertClass($type);
        }
        if ($type instanceof ThisNode) {
            return $this->convertThis($type);
        }
        if ($type instanceof NullNode) {
            return new NullType();
        }
        if ($type instanceof NullableNode) {
            return $this->convertNullable($type);
        }

        if ($type instanceof CallableNode) {
            return $this->convertCallable($type);
        }

        if ($type instanceof ParenthesizedType) {
            return $this->convertParenthesized($type);
        }
        if ($type instanceof LiteralStringNode) {
            return $this->convertLiteralString($type);
        }
        if ($type instanceof LiteralIntegerNode) {
            return $this->convertLiteralInteger($type);
        }
        if ($type instanceof LiteralFloatNode) {
            return $this->convertLiteralFloat($type);
        }
        if ($type instanceof ConstantNode) {
            return $this->convertConstant($type);
        }
        if ($type instanceof ConditionalNode) {
            return $this->convertConditional($type);
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
        if ($type === 'class-string') {
            return new ClassStringType();
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
        if ($type === 'false') {
            return new FalseType();
        }
        if ($type === 'callable') {
            return new CallableType([], new MissingType());
        }

        return new MissingType();
    }

    private function convertArray(ArrayNode $type): Type
    {
        return new ArrayType(new MissingType());
    }

    private function convertList(ListNode $type): Type
    {
        return new ListType(new MixedType());
    }

    private function convertUnion(UnionNode $union): Type
    {
        return new UnionType(...array_map(
            fn (Node $node) => $this->convert($node),
            iterator_to_array($union->types->types())
        ));
    }

    private function convertIntersection(IntersectionNode $type): Type
    {
        return new IntersectionType(...array_map(
            fn (Node $node) => $this->convert($node),
            iterator_to_array($type->types->types())
        ));
    }

    private function convertGeneric(GenericNode $type): Type
    {
        if ($type->type instanceof ArrayNode) {
            $parameters = array_values(iterator_to_array($type->parameters()->types()));
            if (count($parameters) === 1) {
                return new ArrayType(
                    null,
                    $this->convert($parameters[0])
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
        if ($type->type instanceof ScalarNode && $type->type->name->value === 'int') {
            $parameters = array_values(iterator_to_array($type->parameters()->types()));
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

        if ($type->type instanceof ListNode) {
            $parameters = array_values(iterator_to_array($type->parameters()->types()));
            if (count($parameters) === 1) {
                return new ListType(
                    $this->convert($parameters[0])
                );
            }
            return new ListType(new MissingType());
        }

        if ($type->type instanceof ClassNode && $type->type->name->value === 'iterable') {
            $parameters = array_values(iterator_to_array($type->parameters()->types()));

            if (count($parameters) === 1) {
                return new PseudoIterableType(
                    new ArrayKeyType(),
                    $this->convert($parameters[0])
                );
            }
            if (count($parameters) === 2) {
                return new PseudoIterableType(
                    $this->convert($parameters[0]),
                    $this->convert($parameters[1]),
                );
            }
            return new PseudoIterableType();
        }

        $classType = $this->convert($type->type);

        if ($classType instanceof ClassStringType) {
            $parameters = $type->parameters();
            if ($parameters->count()) {
                return new ClassStringType(
                    ClassName::fromString($this->convert(
                        $parameters->types()->first()
                    )->__toString())
                );
            }
            return $classType;
        }

        if (!$classType instanceof ClassType) {
            return new MissingType();
        }

        $parameters = iterator_to_array($type->parameters()->types());

        return new GenericClassType(
            $this->reflector,
            $classType->name(),
            array_map(
                fn (TypeNode $node) => $this->convert($node),
                $parameters
            )
        );
    }

    private function convertClass(ClassNode $typeNode): Type
    {
        $name = $typeNode->name()->toString();

        if ($name === 'never') {
            return new NeverType();
        }

        if ($name === 'static') {
            return new StaticType();
        }

        if ($name === 'self') {
            return new SelfType();
        }

        if ($name === 'iterable') {
            return new PseudoIterableType();
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

        if ($name === 'positive-int') {
            return new IntPositive();
        }

        if ($name === 'negative-int') {
            return new IntNegative();
        }
        $type = new ReflectedClassType(
            $this->reflector,
            ClassName::fromString(
                $typeNode->name()->toString()
            )
        );

        return $this->scope->resolveFullyQualifiedName($type);
    }

    private function convertListBrackets(ListBracketsNode $type): Type
    {
        return new ArrayType($this->convert($type->type));
    }

    private function convertThis(ThisNode $type): Type
    {
        return new ThisType();
    }

    /**
     * @return Type&InvokeableType
     */
    private function convertCallable(CallableNode $callableNode): Type
    {
        $parameters = array_map(function (TypeNode $type) {
            return $this->convert($type);
        }, $callableNode->parameters ? iterator_to_array($callableNode->parameters->types()) : []);

        $type = $this->convert($callableNode->type);

        if ($callableNode->name && $callableNode->name->toString() === 'Closure') {
            return new ClosureType($this->reflector, $parameters, $type);
        }

        return new CallableType($parameters, $type);
    }

    private function convertArrayShape(ArrayShapeNode $type): ArrayShapeType
    {
        $typeMap = [];
        foreach (array_values($type->arrayKeyValueList->arrayKeyValues()) as $index => $keyValue) {
            $key = $keyValue->key ? $keyValue->key->value : $index;
            $typeMap[$key] = $this->convert($keyValue->type);
        }

        return new ArrayShapeType($typeMap);
    }

    private function convertParenthesized(ParenthesizedType $type): Type
    {
        $innerType = $this->convert($type->node);
        return new PhpactorParenthesizedType($innerType);
    }

    private function convertLiteralString(LiteralStringNode $type): Type
    {
        $quote = substr($type->token->value, 0, 1);
        $string = trim($type->token->value, $quote);

        return new StringLiteralType($string);
    }

    private function convertLiteralInteger(LiteralIntegerNode $type): Type
    {
        if ((int)$type->token->value === PHP_INT_MAX) {
            return new IntMaxType();
        }
        return new IntLiteralType((int)$type->token->value);
    }

    private function convertLiteralFloat(LiteralFloatNode $type): Type
    {
        return new FloatLiteralType((float)$type->token->value);
    }

    private function convertConstant(ConstantNode $type): Type
    {
        $classType = $this->convert($type->name);

        return new GlobbedConstantUnionType($classType, $type->constant->value);
    }

    private function convertNullable(NullableNode $type): Type
    {
        return new NullableType($this->convert($type->type));
    }

    private function convertConditional(ConditionalNode $type): Type
    {
        return new ConditionalType($type->variable->name()->toString(), $this->convert($type->isType), $this->convert($type->left), $this->convert($type->right));
    }
}
