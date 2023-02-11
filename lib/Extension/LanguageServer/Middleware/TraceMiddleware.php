<?php

namespace Phpactor\Extension\LanguageServer\Middleware;

use Amp\Promise;
use Phpactor\LanguageServer\Core\Middleware\Middleware;
use Phpactor\LanguageServer\Core\Middleware\RequestHandler;
use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Rpc\ResponseMessage;
use Psr\Log\LoggerInterface;
use function Amp\call;

class TraceMiddleware implements Middleware
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function process(Message $request, RequestHandler $handler): Promise
    {
        return call(function () use ($request, $handler) {
            $this->logger->info($this->format($request), (array)$request);
            /** @phpstan-ignore-next-line */
            $response = yield $handler->handle($request);
            if ($response !== null) {
                $this->logger->info($this->format($response), (array)$response);
            }

            return $response;
        });
    }

    private function format(?Message $request): string
    {
        $encoded = json_encode($request);

        if (false === $encoded) {
            return '<could not encode request>';
        }

        $direction = '>>';

        if ($request instanceof ResponseMessage) {
            $direction = '<<';
        }

        return sprintf('TRAC %s %s', $direction, (function (string $value) {
            if (strlen($value) > 80) {
                return substr($value, 0, 79).'⋯';
            }
            return $value;
        })($encoded));
    }
}
