<?php

namespace Phpactor\WorseReflection\Core\Inference\Context;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Type;

/**
 * @template TMember of ReflectionMember
 */
class MemberAccessContext extends NodeContext
{
    /**
     * @param TMember $member
     */
    public function __construct(
        Symbol $symbol,
        Type $type,
        Type $containerType,
        private ByteOffsetRange $memberNameRange,
        private ReflectionMember $member,
        private ?FunctionArguments $arguments,
    ) {
        parent::__construct($symbol, $type, $containerType);
    }

    /**
     * @return TMember
     */
    public function accessedMember(): ReflectionMember
    {
        return $this->member;
    }

    public function memberNameRange(): ByteOffsetRange
    {
        return $this->memberNameRange;
    }

    public function arguments(): ?FunctionArguments
    {
        return $this->arguments;
    }
}
