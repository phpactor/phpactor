<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Rpc\Response\EchoResponse;

class CacheClearHandler implements Handler
{
    const NAME = 'cache_clear';

    public function __construct(private readonly CacheClear $cacheClear)
    {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
    }

    public function handle(array $arguments): EchoResponse
    {
        $this->cacheClear->clearCache();

        return EchoResponse::fromMessage(sprintf('Cache cleared: %s', $this->cacheClear->cachePath()));
    }
}
