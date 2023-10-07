<?php

namespace Phpactor\Extension\LanguageServer\Middleware;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use function Amp\call;

class TopMiddleware implements Middleware
{
    /**
     * @var array<string,int>
     */
    private array $requests = [];

    /**
     * @return array<string,int>
     */
    public function requests(): array
    {
        return array_filter($this->requests, fn (int $count) => $count > 0);
    }

    public function process(Message $request, RequestHandler $handler): Promise
    {
        return call(function () use ($request, $handler) {
            $this->add($request, 1);
            $response = yield $handler->handle($request);
            $this->add($request, -1);
            return $response;
        });
    }

    private function add(Message $request, int $amount): void
    {
        if ($request instanceof RequestMessage) {
            if (!isset($this->requests[$request->method])) {
                $this->requests[$request->method] = 0;
            }

            $this->requests[$request->method] += $amount;
        }
    }
}
