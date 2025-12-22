<?php

namespace Phpactor\Extension\Symfony\Model;

use Phpactor\WorseReflection\Core\Type;

class SymfonyContainerParameter
{
    public function __construct(
        public string $id,
        public Type $type
    ) {
    }
}
