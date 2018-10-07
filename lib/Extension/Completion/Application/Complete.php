<?php

namespace Phpactor\Extension\Completion\Application;

use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;

class Complete
{
    /**
     * @var Completor
     */
    private $competor;

    public function __construct(Completor $competor)
    {
        $this->competor = $competor;
    }

    public function complete(string $source, int $offset): array
    {
        $suggestions = iterator_to_array($this->competor->complete($source, $offset));
        $suggestions = array_map(function (Suggestion $suggestion) {
            return $suggestion->toArray();
        }, $suggestions);

        return [
            'suggestions' => $suggestions,

            // deprecated
            'issues' => [],
        ];
    }
}
