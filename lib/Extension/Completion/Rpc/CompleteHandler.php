<?php

namespace Phpactor\Extension\Completion\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Completion\Application\Complete;
use Phpactor\Extension\Rpc\Response\ReturnResponse;

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

    public function configure(Resolver $resolver)
    {
        $resolver->setRequired([
            self::PARAM_SOURCE,
            self::PARAM_OFFSET,
        ]);
    }

    public function handle(array $arguments)
    {
        $suggestions = $this->complete->complete($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_OFFSET]);

        return ReturnResponse::fromValue($suggestions);
    }
}
