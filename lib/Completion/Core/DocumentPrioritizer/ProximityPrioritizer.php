<?php

namespace Phpactor\Completion\Core\DocumentPrioritizer;

use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\TextDocumentUri;

/**
 * Prioritize based on the distance between two paths.
 *
 * - Remove the common path segment
 * - Total of remaining elements is the distance
 */
class ProximityPrioritizer implements DocumentPrioritizer
{
    public function priority(?TextDocumentUri $one, ?TextDocumentUri $two): int
    {
        if (null === $one) {
            return Suggestion::PRIORITY_LOW;
        }

        if (null === $two) {
            return Suggestion::PRIORITY_LOW;
        }

        $weight = $this->resolveWeight($one, $two);

        $range = Suggestion::PRIORITY_LOW - Suggestion::PRIORITY_MEDIUM;

        return (int)(Suggestion::PRIORITY_MEDIUM + $range - $range * $weight);
    }

    private function resolveWeight(TextDocumentUri $one, TextDocumentUri $two): float
    {
        $e1 = explode('/', $one->path());
        $e2 = explode('/', $two->path());
        
        foreach ($e1 as $index => $segment) {
            if (isset($e2[$index]) && $e2[$index] === $segment) {
                unset($e1[$index], $e2[$index]);
            }
        }

        $count1 = count($e1);
        $count2 = count($e2);

        $distance = $count1 + $count2;

        if ($distance === 0) {
            return 1;
        }
        
        $max = max($count1, $count2);
        $weight = $max / $distance;
        return 1 - $weight;
    }
}
