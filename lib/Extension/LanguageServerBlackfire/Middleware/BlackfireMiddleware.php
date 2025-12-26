<?php

namespace Phpactor\Extension\LanguageServerBlackfire\Middleware;

use Amp\Promise;
use Phpactor\Extension\LanguageServerBlackfire\BlackfireProfiler;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use function Amp\call;

class BlackfireMiddleware implements Middleware
{
    public function __construct(private readonly BlackfireProfiler $profiler)
    {
    }

    public function process(Message $request, RequestHandler $handler): Promise
    {
        return call(function () use ($request, $handler) {
            if (!$this->profiler->started()) {
                return $handler->handle($request);
            }
            $this->profiler->enable();

            $response = yield $handler->handle($request);

            if ($this->profiler->started()) {
                $this->profiler->disable();
            }
            return $response;
        });
    }
}
