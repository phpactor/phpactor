<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\ArrayKeyType;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BinLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\EnumBackedCaseType;
use Phpactor\WorseReflection\Core\Type\EnumCaseType;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\FloatLiteralType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\GeneratorType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\HexLiteralType;
use Phpactor\WorseReflection\Core\Type\IntLiteralType;
use Phpactor\WorseReflection\Core\Type\IntNegative;
use Phpactor\WorseReflection\Core\Type\IntPositive;
use Phpactor\WorseReflection\Core\Type\IntRangeType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NeverType;
use Phpactor\WorseReflection\Core\Type\NotType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\NumericType;
use Phpactor\WorseReflection\Core\Type\ObjectType;
use Phpactor\WorseReflection\Core\Type\OctalLiteralType;
use Phpactor\WorseReflection\Core\Type\ParenthesizedType;
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

class TypeFactory
{
    public static function fromStringWithReflector(string $type, Reflector $reflector): Type
    {
        return self::fromString($type, $reflector);
    }

    public static function fromString(string $type, Reflector $reflector = null): Type
    {
        if (str_starts_with($type, '?')) {
            return self::nullable(self::typeFromString(substr($type, 1)));
        }

        return self::typeFromString($type, $reflector);
    }

    /**
     * @param mixed $value
     */
    public static function fromValue($value): Type
    {
        if (is_int($value)) {
            return self::intLiteral($value);
        }

        if (is_string($value)) {
            return self::stringLiteral($value);
        }

        if (is_float($value)) {
            return self::floatLiteral($value);
        }

        if (is_array($value)) {
            return self::array();
        }

        if (is_bool($value)) {
            return self::boolLiteral($value);
        }

        if (null === $value) {
            return self::null();
        }

        if (is_callable($value)) {
            return self::callable();
        }

        if (is_object($value)) {
            return self::class(ClassName::fromString(get_class($value)));
        }

        if (is_resource($value)) {
            return self::resource();
        }

        return self::unknown();
    }

    public static function union(Type ...$types): UnionType
    {
        return new UnionType(...$types);
    }

    public static function intersection(Type ...$types): IntersectionType
    {
        return new IntersectionType(...$types);
    }

    public static function null(): NullType
    {
        return new NullType();
    }

    public static function unknown(): MissingType
    {
        return new MissingType();
    }

    public static function string(): StringType
    {
        return new StringType();
    }

    public static function int(): IntType
    {
        return new IntType();
    }

    public static function float(): FloatType
    {
        return new FloatType();
    }

    public static function array(?Type $iterableType = null): ArrayType
    {
        return new ArrayType($iterableType ? $iterableType : new MissingType());
    }

    public static function mixed(): MixedType
    {
        return new MixedType();
    }

    public static function bool(): BooleanType
    {
        return new BooleanType();
    }

    public static function object(): ObjectType
    {
        return new ObjectType();
    }

    public static function void(): VoidType
    {
        return new VoidType();
    }

    public static function resource(): ResourceType
    {
        return new ResourceType();
    }

    public static function iterable(): PseudoIterableType
    {
        return new PseudoIterableType();
    }

    /**
     * @param string|ClassName $className
     */
    public static function class($className, ClassReflector $reflector = null): ClassType
    {
        $name = ClassName::fromUnknown($className);
        if ($reflector) {
            return new ReflectedClassType($reflector, $name);
        }
        return new ClassType($name);
    }

    /**
     * @param string|ClassName $className
     */
    public static function reflectedClass(ClassReflector $reflector, $className): ReflectedClassType
    {
        return new ReflectedClassType($reflector, ClassName::fromUnknown($className));
    }

    public static function undefined(): MissingType
    {
        return new MissingType();
    }

    public static function callable(): CallableType
    {
        return new CallableType([], new MissingType());
    }

    public static function nullable(Type $type): NullableType
    {
        return new NullableType($type);
    }

    public static function collection(ClassReflector $reflector, string $classType, string $iterableType): GenericClassType
    {
        return new GenericClassType(
            $reflector,
            ClassName::fromString($classType),
            [
                self::fromString($iterableType)
            ]
        );
    }

    public static function intLiteral(int $value): IntLiteralType
    {
        return new IntLiteralType($value);
    }

    public static function stringLiteral(string $value): StringLiteralType
    {
        return new StringLiteralType($value);
    }

    public static function floatLiteral(float $value): FloatLiteralType
    {
        return new FloatLiteralType($value);
    }

    public static function boolLiteral(bool $value): BooleanLiteralType
    {
        return new BooleanLiteralType($value);
    }

    /**
     * @param array<array-key,Type> $elements
     */
    public static function arrayLiteral(array $elements): ArrayLiteral
    {
        return new ArrayLiteral($elements);
    }

    public static function fromNumericString(string $value): NumericType
    {
        return self::convertNumericStringToInternalType(
            // Strip PHP 7.4 underscorse separator before comparison
            str_replace('_', '', $value)
        );
    }

    public static function not(Type $type): NotType
    {
        return new NotType($type);
    }

    public static function unionEmpty(): UnionType
    {
        return new UnionType(
            new IntLiteralType(0),
            new FloatLiteralType(0.0),
            new StringLiteralType(''),
            new StringLiteralType('0'),
            new ArrayLiteral([]),
            new BooleanLiteralType(false),
            new NullType()
        );
    }

    /**
     * @param mixed[] $values
     * @return Type[]
     */
    public static function fromValues(array $values): array
    {
        return array_map(fn ($value) => self::fromValue($value), $values);
    }

    public static function parenthesized(Type $type): ParenthesizedType
    {
        return new ParenthesizedType($type);
    }

    public static function toAggregateOrUnion(Type $type): AggregateType
    {
        if ($type instanceof AggregateType) {
            return $type;
        }

        return UnionType::toUnion($type);
    }

    public static function toAggregateOrIntersection(Type $type): AggregateType
    {
        if ($type instanceof AggregateType) {
            return $type;
        }

        return IntersectionType::toIntersection($type);
    }

    public static function generator(Reflector $reflector, Type $keyType, Type $valueType): GenericClassType
    {
        return new GeneratorType($reflector, $keyType, $valueType);
    }

    public static function arrayKey(): ArrayKeyType
    {
        return new ArrayKeyType();
    }

    /**
     * @param array<array-key,Type> $typeMap
     */
    public static function arrayShape(array $typeMap): ArrayShapeType
    {
        return new ArrayShapeType($typeMap);
    }

    public static function list(?Type $iterabletype = null): ArrayType
    {
        return new ArrayType(self::int(), $iterabletype ?: self::mixed());
    }

    public static function never(): NeverType
    {
        return new NeverType();
    }

    public static function false(): FalseType
    {
        return new FalseType();
    }

    public static function classString(string $classFqn): ClassStringType
    {
        return new ClassStringType(ClassName::fromString($classFqn));
    }

    public static function static(?Type $type = null): StaticType
    {
        return new StaticType($type);
    }

    public static function this(?Type $type = null): ThisType
    {
        return new ThisType($type);
    }

    public static function enumCaseType(Reflector $reflector, ClassType $enumType, string $name): EnumCaseType
    {
        return new EnumCaseType($reflector, $enumType, $name);
    }

    public static function enumBackedCaseType(Reflector $reflector, ClassType $enumType, string $name, Type $value): EnumBackedCaseType
    {
        return new EnumBackedCaseType($reflector, $enumType, $name, $value);
    }

    public static function intRange(Type $lower, Type $upper): IntRangeType
    {
        return new IntRangeType($lower, $upper);
    }

    public static function intPositive(): IntPositive
    {
        return new IntPositive();
    }

    public static function intNegative(): IntNegative
    {
        return new IntNegative();
    }

    private static function typeFromString(string $type, Reflector $reflector = null): Type
    {
        if ('' === $type) {
            return self::unknown();
        }

        if ($type === 'string') {
            return self::string();
        }

        if ($type === 'int') {
            return self::int();
        }

        if ($type === 'float') {
            return self::float();
        }

        if ($type === 'array') {
            return self::array();
        }

        if ($type === 'bool') {
            return self::bool();
        }

        if ($type === 'mixed') {
            return self::mixed();
        }

        if ($type === 'null') {
            return self::null();
        }

        if ($type === 'object') {
            return self::object();
        }

        if ($type === 'void') {
            return self::void();
        }

        if ($type === 'callable') {
            return self::callable();
        }

        if ($type === 'resource') {
            return self::resource();
        }

        if ($type === 'iterable') {
            return self::iterable();
        }

        if ($type === 'self') {
            return new SelfType();
        }

        if ($type === 'static') {
            return new StaticType();
        }

        if ($type === 'class-string') {
            return new ClassStringType();
        }

        if ($type === '$this') {
            return new StaticType();
        }

        if ($type === 'never') {
            return new NeverType();
        }

        if ($type === 'false') {
            return new FalseType();
        }

        return self::class(ClassName::fromString($type), $reflector);
    }


    private static function convertNumericStringToInternalType(string $value): NumericType
    {
        if (1 === preg_match('/^[1-9][0-9]*$/', $value)) {
            return self::intLiteral((int)$value);
        }
        if (1 === preg_match('/^0[xX][0-9a-fA-F]+$/', $value)) {
            return new HexLiteralType($value);
        }
        if (1 === preg_match('/^0[0-7]+$/', $value)) {
            return new OctalLiteralType($value);
        }
        if (1 === preg_match('/^0[bB][01]+$/', $value)) {
            return new BinLiteralType($value);
        }

        if (!str_contains($value, '.')) {
            return self::intLiteral((int)$value);
        }

        return self::floatLiteral((float)$value);
    }
}
