<?php

namespace Phpactor\Extension\Completion\Rpc;

use Phpactor\Rpc\Handler;
use Phpactor\Extension\Completion\Application\Complete;
use Phpactor\Rpc\Response\ReturnResponse;

class CompleteHandler implements Handler
{
    const NAME = 'complete';
    const PARAM_SOURCE = 'source';
    const PARAM_OFFSET = 'offset';

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
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_SOURCE => null,
            self::PARAM_OFFSET => null,
        ];
    }

    public function handle(array $arguments)
    {
        $suggestions = $this->complete->complete($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_OFFSET]);

        return ReturnResponse::fromValue($suggestions);
    }
}
