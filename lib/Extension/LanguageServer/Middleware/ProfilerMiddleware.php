<?php

namespace Phpactor\Extension\LanguageServer\Middleware;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\NotificationMessage;
use Phpactor\LanguageServer\Core\Rpc\RequestMessage;
use Psr\Log\LoggerInterface;
use function Amp\call;

class ProfilerMiddleware implements Middleware
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(Message $request, RequestHandler $handler): Promise
    {
        return call(function () use ($request, $handler) {
            if ($request instanceof NotificationMessage) {
                $this->logger->info(sprintf('PROFILER >> notification [%s]', $request->method));
            }

            if ($request instanceof RequestMessage) {
                $this->logger->info(sprintf(
                    'PROFILER >> request #%d [%s]',
                    $request->id,
                    $request->method,
                ));
            }

            $start = microtime(true);
            $response = yield $handler->handle($request);
            $elapsed = microtime(true) - $start;

            if ($request instanceof NotificationMessage) {
                $this->logger->info(sprintf('PROFILER << notification [%s] %ss', $request->method, number_format($elapsed, 4)));
            }

            if ($request instanceof RequestMessage) {
                $this->logger->info(sprintf(
                    'PROFILER << request #%d [%s] %ss',
                    $request->id,
                    $request->method,
                    number_format($elapsed, 4)
                ));
            }

            return $response;
        });
    }
}
