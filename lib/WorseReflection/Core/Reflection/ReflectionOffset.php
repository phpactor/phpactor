<?php

namespace Phpactor\WorseReflection\Core\Reflection;

use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;

interface ReflectionOffset
{
    public static function fromFrameAndSymbolContext($frame, $symbolInformation);

    public function frame(): Frame;

    public function nodeContext(): NodeContext;
}
