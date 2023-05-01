<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\Docblock;

use Generator;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockIncorrectClassGenericDiagnostic;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingClassGenericDiagnostic;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

class ClassGenericDiagnosticHelper
{
    /**
     * @return Generator<Diagnostic>
     */
    public function diagnostics(ReflectionClassLike $class): Generator
    {
        if ($class instanceof ReflectionClass) {
            yield from $this->fromReflectionClass($class);
        }
    }

    /**
     * @return Generator<Diagnostic>
     */
    private function fromReflectionClass(ClassReflector $reflector, ByteOffsetRange $range, ReflectionClass $class, ?ReflectionClass $parentClass): Generator
    {
        if (!$parentClass) {
            return;
        }

        $templateMap = $parentClass->templateMap();

        if (!count($templateMap)) {
            return;
        }

        $extendTagTypes = $class->docblock()->extends();
        $extendTagTypes = array_filter($extendTagTypes, fn (Type $extendTagType) => $parentClass->type()->accepts($extendTagType)->isTrue());
        $defaultGenericType = new GenericClassType(
            $reflector,
            $parentClass->name(),
            array_map(
                fn (Type $type) => $type instanceof MissingType ? new MixedType() : $type,
                $templateMap->toArguments(),
            )
        );

        if (0 === count($extendTagTypes)) {
            yield new DocblockMissingClassGenericDiagnostic(
                $range,
                $class->name(),
                $defaultGenericType
            );
            return;
        }


        $extendTagType = $extendTagTypes[0];
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
