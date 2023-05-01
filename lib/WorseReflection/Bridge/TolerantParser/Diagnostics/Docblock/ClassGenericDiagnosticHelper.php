<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\Docblock;

use Generator;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockIncorrectClassGenericDiagnostic;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingClassGenericDiagnostic;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

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
            yield from $this->fromReflectionClass($reflector, $range, $class, $parentClass, $class->docblock()->extends());
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
        array $genericTypes
    ): Generator {
        if (!$parentClass) {
            return;
        }

        $templateMap = $parentClass->templateMap();

        if (!count($templateMap)) {
            return;
        }

        $genericTypes = array_filter($genericTypes, fn (Type $extendTagType) => $parentClass->type()->accepts($extendTagType)->isTrue());
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
                $defaultGenericType
            );
            return;
        }


        $extendTagType = $genericTypes[0];
        if ($parentClass->type()->upcastToGeneric()->accepts($extendTagType)->isFalse()) {
            yield new DocblockIncorrectClassGenericDiagnostic(
                $range,
                $extendTagType,
                $defaultGenericType
            );
            return;
        }

        if (!$extendTagType instanceof GenericClassType) {
            yield new DocblockIncorrectClassGenericDiagnostic(
                $range,
                $extendTagType,
                $defaultGenericType
            );
            return;
        }

        if ($defaultGenericType->accepts($extendTagType)->isTrue()) {
            return;
        }

        yield new DocblockIncorrectClassGenericDiagnostic(
            $range,
            $extendTagType,
            $defaultGenericType
        );
    }
}
