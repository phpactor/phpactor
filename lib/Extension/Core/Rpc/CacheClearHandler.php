<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\MapResolver\Resolver;

class CacheClearHandler implements Handler
{
    public const NAME = 'cache_clear';

    private CacheClear $cacheClear;

    public function __construct(CacheClear $cacheClear)
    {
        $this->cacheClear = $cacheClear;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
    }

    public function handle(array $arguments)
    {
        $this->cacheClear->clearCache();

        return EchoResponse::fromMessage(sprintf('Cache cleared: %s', $this->cacheClear->cachePath()));
    }
}
