<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

interface BitwiseOperable
{
    public function shiftRight(Type $right): Type;
    public function shiftLeft(Type $right): Type;
    public function bitwiseXor(Type $right): Type;
    public function bitwiseOr(Type $right): Type;
    public function bitwiseAnd(Type $right): Type;
    public function bitwiseNot(): Type;
}
