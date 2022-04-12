<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\TypeUtil;

trait ArrayTypeTrait
{
    /**
     * @param array<array-key,Type> $typeMap
     */
    protected function resolveValueType(array $typeMap): Type
    {
        $valueType = null;
        foreach ($typeMap as $type) {
            $type = TypeUtil::generalize($type);
            if ($valueType === null) {
                $valueType = $type;
                continue;
            }

            if ($valueType != $type) {
                return new MixedType();
            }
        }

        return $valueType ?: new MissingType();
    }
}
