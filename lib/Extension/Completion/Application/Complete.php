<?php

namespace Phpactor\Extension\Completion\Application;

use Phpactor\Completion\Core\Completor;

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
        $result = $this->competor->complete($source, $offset);

        return [
            'suggestions' => $result->suggestions()->toArray(),
            'issues' => $result->issues()->toArray(),
        ];
    }
}
