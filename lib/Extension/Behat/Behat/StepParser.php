<?php

namespace Phpactor\Extension\Behat\Behat;

class StepParser
{
    /**
     * @return string[]
     */
    public function parseSteps(string $string): array
    {
        $keywords = ['Given','When','Then','And','But'];
        return $this->extractSteps($keywords, $string);
    }

    /**
     * @return string[]
     * @param string[] $keywords
     */
    private function extractSteps(array $keywords, string $string): array
    {
        preg_match_all('{('.implode('|', $keywords).')\s*(.*)}', $string, $matches);

        return $matches[2] ?? [];
    }
}
