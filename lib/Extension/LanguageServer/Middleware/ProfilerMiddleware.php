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
    public function __construct(private LoggerInterface $logger, private bool $trace = false)
    {
    }

    public function process(Message $request, RequestHandler $handler): Promise
    {
        return call(function () use ($request, $handler) {
            $context = [];
            if ($this->trace) {
                $context['trace'] = true;
                $context['body'] = json_encode($request);
            }
            if ($request instanceof NotificationMessage) {
                $this->info(sprintf('PROF        >> notification [%s]', $request->method), $context);
            }

            if ($request instanceof RequestMessage) {
                $this->info(sprintf(
                    'PROF        >> request #%d [%s]',
                    $request->id,
                    $request->method,
                ), $context);
            }

            $start = microtime(true);
            $response = yield $handler->handle($request);
            $elapsed = microtime(true) - $start;

            if ($this->trace) {
                $context['trace'] = true;
                $context['body'] = json_encode($response);
            }

            if ($request instanceof NotificationMessage) {
                $this->info(sprintf('PROF %-6s << notification [%s]', number_format($elapsed, 4), $request->method), $context);
            }

            if ($request instanceof RequestMessage) {
                $this->info(sprintf(
                    'PROF %-6s << request #%d [%s]',
                    number_format($elapsed, 4),
                    $request->id,
                    $request->method,
                ), $context);
            }

            return $response;
        });
    }
    /**
     * @param array<string,mixed> $context
     */
    private function info(string $message, array $context): void
    {
        $this->logger->info($message, $context);
    }
}
