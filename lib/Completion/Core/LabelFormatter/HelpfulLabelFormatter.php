<?php

namespace Phpactor\Completion\Core\LabelFormatter;

use Phpactor\Completion\Core\LabelFormatter;

class HelpfulLabelFormatter implements LabelFormatter
{
    /**
     * @param array<string,bool> $seen
     */
    public function format(string $name, array $seen, int $offset = 1): string
    {
        $parts = explode('\\', $name);
        $end = array_pop($parts);
        if (count($parts) === 0) {
            return $end;
        }

        // the offset is more than the number of parts -- this should not
        // happen as it implies two identically named classes
        if (count($parts) < $offset) {
            return $end;
        }

        $label = sprintf('%s (%s)', $end, implode('\\', array_slice($parts, 0, $offset)));

        if (isset($seen[$label])) {
            return self::format($name, $seen, $offset + 1);
        }

        return $label;
    }
}
