<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BinLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanLiteralType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\FloatLiteralType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\HexLiteralType;
use Phpactor\WorseReflection\Core\Type\IntLiteralType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\NullableType;
use Phpactor\WorseReflection\Core\Type\ObjectType;
use Phpactor\WorseReflection\Core\Type\OctalLiteralType;
use Phpactor\WorseReflection\Core\Type\PrimitiveIterableType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\ResourceType;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Type\StaticType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;
use Phpactor\WorseReflection\Core\Type\StringType;
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
        if ('?' === substr($type, 0, 1)) {
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

    public static function array(?string $iterableType = null): ArrayType
    {
        return new ArrayType($iterableType ? self::fromString($iterableType) : new MissingType());
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

    public static function iterable(): PrimitiveIterableType
    {
        return new PrimitiveIterableType();
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

    public static function fromNumericString(string $value): Type
    {
        return self::convertNumericStringToInternalType(
            // Strip PHP 7.4 underscorse separator before comparison
            str_replace('_', '', $value)
        );
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

        return self::class(ClassName::fromString($type), $reflector);
    }

    
    private static function convertNumericStringToInternalType(string $value): Type
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

        if (false === strpos($value, '.')) {
            return self::intLiteral((int)$value);
        }

        return self::floatLiteral((float)$value);
    }
}
