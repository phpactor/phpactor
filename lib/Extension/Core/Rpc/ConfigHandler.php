<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\InformationResponse;

class ConfigHandler implements Handler
{
    const CONFIG = 'config';

    public function __construct(private readonly array $config)
    {
    }

    public function name(): string
    {
        return self::CONFIG;
    }

    public function configure(Resolver $resolver): void
    {
    }

    public function handle(array $arguments)
    {
        return InformationResponse::fromString(json_encode($this->config, JSON_PRETTY_PRINT));
    }
}
