<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\Complete;
use Phpactor\Rpc\Editor\ReturnAction;

class CompleteHandler implements Handler
{
    /**
     * @var Complete
     */
    private $complete;

    public function __construct(Complete $complete)
    {
        $this->complete = $complete;
    }

    public function name(): string
    {
        return 'complete';
    }

    public function defaultParameters(): array
    {
        return [
            'source' => null,
            'offset' => null,
        ];
    }

    public function handle(array $arguments)
    {
        $suggestions = $this->complete->complete($arguments['source'], $arguments['offset']);

        return ReturnAction::fromValue($suggestions);
    }
}

