<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Rpc\Response\EchoResponse;

class CacheClearHandler implements Handler
{
    const NAME = 'cache_clear';

    /**
     * @var CacheClear
     */
    private $cacheClear;

    public function __construct(CacheClear $cacheClear)
    {
        $this->cacheClear = $cacheClear;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver)
    {
    }

    public function handle(array $arguments)
    {
        $this->cacheClear->clearCache();

        return EchoResponse::fromMessage(sprintf('Cache cleared: %s', $this->cacheClear->cachePath()));
    }
}
