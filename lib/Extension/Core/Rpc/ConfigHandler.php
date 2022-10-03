<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\InformationResponse;
use Phpactor\MapResolver\Resolver;

class ConfigHandler implements Handler
{
    public const CONFIG = 'config';

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
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
