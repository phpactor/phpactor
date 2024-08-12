<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\Docblock;

use Generator;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockIncorrectClassGenericDiagnostic;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingClassGenericDiagnostic;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Reflector;

final class ClassGenericDiagnosticHelper
{
    /**
     * @return Generator<Diagnostic>
     */
    public function diagnosticsForExtends(
        ClassReflector $reflector,
        ByteOffsetRange $range,
        ReflectionClassLike $class,
        ?ReflectionClassLike $parentClass
    ): Generator {
        if ($class instanceof ReflectionClass) {
            yield from $this->fromReflectionClass($reflector, $range, $class, $parentClass, $class->docblock()->extends(), '@extends');
        }
    }
    /**
     * @return Generator<Diagnostic>
     */
    public function diagnosticsForImplements(Reflector $reflector, ByteOffsetRange $range, ReflectionClassLike $class, ?ReflectionClassLike $genericClass): Generator
    {
        if ($class instanceof ReflectionClass) {
            yield from $this->fromReflectionClass(
                $reflector,
                $range,
                $class,
                $genericClass,
                $class->docblock()->implements(),
                '@implements'
            );
        }
    }

    /**
     * @return Generator<Diagnostic>
     * @param Type[] $genericTypes
     */
    private function fromReflectionClass(
        ClassReflector $reflector,
        ByteOffsetRange $range,
        ReflectionClassLike $class,
        ?ReflectionClassLike $parentClass,
        array $genericTypes,
        string $tagName
    ): Generator {
        if (!$parentClass) {
            return;
        }

        $templateMap = $parentClass->templateMap();

        if (!count($templateMap)) {
            return;
        }

        $genericTypes = array_filter(
            $genericTypes,
            fn (Type $extendTagType) => $parentClass->type()->accepts($extendTagType)->isTrue()
        );

        $defaultGenericType = new GenericClassType(
            $reflector,
            $parentClass->name(),
            array_map(
                fn (Type $type) => $type instanceof MissingType ? new MixedType() : $type,
                $templateMap->toArguments(),
            )
        );

        if (0 === count($genericTypes)) {
            yield new DocblockMissingClassGenericDiagnostic(
                $range,
                $class->name(),
                $defaultGenericType,
                $tagName
            );
            return;
        }


        $extendTagType = $genericTypes[array_key_first($genericTypes)];

        // if generic uses a templateed type, then replace the templated type
        // with the type restriction if it exists (e.g. replace T with Foo if
        // @template T of Foo)
        if ($extendTagType instanceof GenericClassType) {
            $classTemplateMap = $class->templateMap();
            $extendTagType = $extendTagType->withArguments(array_map(function (Type $type) use ($classTemplateMap) {
                return $classTemplateMap->getOrGiven($type);
            }, $extendTagType->arguments()));
        }

        if ($parentClass->type()->upcastToGeneric()->accepts($extendTagType)->isFalse()) {
            yield new DocblockIncorrectClassGenericDiagnostic(
                $range,
                $extendTagType,
                $defaultGenericType,
                $tagName
            );
            return;
        }

        if (!$extendTagType instanceof GenericClassType) {
            yield new DocblockIncorrectClassGenericDiagnostic(
                $range,
                $extendTagType,
                $defaultGenericType,
                $tagName
            );
            return;
        }

        if ($defaultGenericType->accepts($extendTagType)->isTrue()) {
            return;
        }

        yield new DocblockIncorrectClassGenericDiagnostic(
            $range,
            $extendTagType,
            $defaultGenericType,
            $tagName
        );
    }
}
