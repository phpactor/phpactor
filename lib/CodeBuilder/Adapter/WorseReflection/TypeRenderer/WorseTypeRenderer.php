<?php

namespace Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer;

use Phpactor\WorseReflection\Core\Type;

interface WorseTypeRenderer
{
    public function render(Type $type): ?string;
}
