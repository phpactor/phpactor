<?php

namespace Phpactor\Extension\Core\Rpc;

use Phpactor\Container\Schema;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\CacheClear;
use Phpactor\Extension\Rpc\Response\EchoResponse;

class CacheClearHandler implements Handler
{
    const CACHE_CLEAR = 'cache_clear';

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
        return self::CACHE_CLEAR;
    }

    public function configure(Schema $schema): void
    {
    }

    public function handle(array $arguments)
    {
        $this->cacheClear->clearCache();

        return EchoResponse::fromMessage(sprintf('Cache cleared: %s', $this->cacheClear->cachePath()));
    }
}
