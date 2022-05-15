<?php

namespace Phpactor\Extension\Behat\Behat;

class StepScorer
{
    /**
     * @param Step[] $steps
     */
    public function scoreSteps(array $steps, $partial)
    {
        $items = array_filter(array_map('trim', explode(' ', $partial)));

        $scored = [];
        foreach ($steps as $step) {
            $score = 0;
            foreach ($items as $item) {
                $score += substr_count($step->pattern(), $item);
            }

            $scored[$step->pattern()] = $score;
        }

        return $scored;
    }
}
