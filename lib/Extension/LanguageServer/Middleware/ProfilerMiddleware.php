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
                $this->info(sprintf('       PROF >> notification [%s]', $request->method));
            }

            if ($request instanceof RequestMessage) {
                $this->info(sprintf(
                    '       PROF >> request #%d [%s]',
                    $request->id,
                    $request->method,
                ));
            }

            $start = microtime(true);
            $response = yield $handler->handle($request);
            $elapsed = microtime(true) - $start;

            if ($request instanceof NotificationMessage) {
                $this->info(sprintf('%-6s PROF << notification [%s]', number_format($elapsed, 4), $request->method));
            }

            if ($request instanceof RequestMessage) {
                $this->info(sprintf(
                    '%-6s PROF << request #%d [%s]',
                    number_format($elapsed, 4),
                    $request->id,
                    $request->method,
                ));
            }

            return $response;
        });
    }

    private function info(string $format, string ...$args): void
    {
        $message = sprintf($format, ...$args);
        $this->logger->info(sprintf('[%-15s] %s', microtime(true), $message));
    }
}
