<?php

namespace Phpactor\Extension\Behat\Behat;

class StepParser
{
    public function parseSteps(string $string)
    {
        $keywords = ['Given','When','Then','And','But'];
        return $this->extractSteps($keywords, $string);
    }

    private function extractSteps(array $keywords, string $string)
    {
        preg_match_all('{('.implode('|', $keywords).')\s*(.*)}', $string, $matches);

        if (isset($matches[2])) {
            return $matches[2];
        }

        return [];
    }
}
