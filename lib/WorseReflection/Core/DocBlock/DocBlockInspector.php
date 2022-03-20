<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

interface DocBlockInspector
{
    public function typesForMethod(string $docblock, string $methodName): array;
}
