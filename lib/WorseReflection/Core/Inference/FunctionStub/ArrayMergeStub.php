<?php

namespace Phpactor\WorseReflection\Core\Inference\FunctionStub;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStub;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\TypeUtil;

class ArrayMergeStub implements FunctionStub
{
    public function resolve(
        Frame $frame,
        NodeContext $context,
        FunctionArguments $args
    ): NodeContext {
        $types = [];
        $isLiteralSet = true;
        $typeMap = [];
        foreach ($args as $arg) {
            $type = $arg->type();
            if (!$type instanceof ArrayLiteral) {
                $isLiteralSet = false;
                break;
            }
            $typeMap = array_merge($typeMap, $type->types());
        }

        if ($isLiteralSet) {
            return $context->withType(TypeFactory::arrayLiteral($typeMap));
        }

        $keys = $values = [];
        foreach ($args as $arg) {
            $type = $arg->type();
            if (!$type instanceof ArrayType) {
                continue;
            }
            $keys[] = $type->keyType;
            $values[] = $type->valueType;
        }

        if ($values) {
            return $context->withType(
                new ArrayType(
                    TypeFactory::union(...$keys),
                    TypeFactory::union(...$values),
                )
            );
        }

        return $context->withType(TypeUtil::generalTypeFromTypes($types));
    }
}
