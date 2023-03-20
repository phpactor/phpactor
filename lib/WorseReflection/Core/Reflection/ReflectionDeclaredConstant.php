<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;

interface ReflectionDeclaredConstant
{
    public function name(): Name;
    public function type(): Type;
    public function sourceCode(): SourceCode;
    public function docblock(): DocBlock;
    public function position(): ByteOffsetRange;
}
