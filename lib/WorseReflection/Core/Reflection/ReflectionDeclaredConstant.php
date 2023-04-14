<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionDeclaredConstant
{
    public function name(): Name;
    public function type(): Type;
    public function sourceCode(): TextDocument;
    public function docblock(): DocBlock;
    public function position(): ByteOffsetRange;
}
