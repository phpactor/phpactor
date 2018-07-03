<?php

namespace Phpactor\Extension\Completion\Rpc;

use Phpactor\Container\Schema;
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

    public function configure(Schema $schema): void
    {
        $schema->setDefaults([
            self::PARAM_SOURCE => null,
            self::PARAM_OFFSET => null,
        ]);
    }

    public function handle(array $arguments)
    {
        $suggestions = $this->complete->complete($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_OFFSET]);

        return ReturnResponse::fromValue($suggestions);
    }
}
