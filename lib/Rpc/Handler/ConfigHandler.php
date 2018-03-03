<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Application\Status;
use Phpactor\Rpc\Response\EchoResponse;
use Phpactor\Rpc\Response\InformationResponse;

class ConfigHandler implements Handler
{
    const CONFIG = 'config';

    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function name(): string
    {
        return self::CONFIG;
    }

    public function defaultParameters(): array
    {
        return [];
    }

    public function handle(array $arguments)
    {
        return InformationResponse::fromString(json_encode($this->config, JSON_PRETTY_PRINT));
    }
}
