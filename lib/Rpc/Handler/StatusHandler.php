<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\Status;
use Phpactor\Rpc\Response\EchoResponse;

class StatusHandler implements Handler
{
    const STATUS = 'status';

    /**
     * @var Status
     */
    private $status;

    public function __construct(Status $status)
    {
        $this->status = $status;
    }

    public function name(): string
    {
        return self::STATUS;
    }

    public function defaultParameters(): array
    {
        return [];
    }

    public function handle(array $arguments)
    {
        $diagnostics = $this->status->check();

        return EchoResponse::fromMessage(
            implode(PHP_EOL, [
                implode(PHP_EOL, array_map(function (string $message) {
                    return '[✔] ' . $message;
                }, $diagnostics['good'])),
                implode(PHP_EOL, array_map(function (string $message) {
                    return '[✘] ' . $message;
                }, $diagnostics['bad'])),
            ])
        );
    }
}
