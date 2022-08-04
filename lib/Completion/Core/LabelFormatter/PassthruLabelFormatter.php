<?php

namespace Phpactor\Completion\Core\LabelFormatter;

use Phpactor\Completion\Core\LabelFormatter;

class PassthruLabelFormatter implements LabelFormatter
{
    public function format(string $name, array $seen, int $offset = 1): string
    {
        return $name;
    }
}
