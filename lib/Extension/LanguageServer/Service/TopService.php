<?php

namespace Phpactor\Extension\LanguageServer\Service;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServer\Middleware\TopMiddleware;
use Phpactor\LanguageServer\Core\Service\ServiceProvider;
use Psr\Log\LoggerInterface;
use function Amp\call;
use function Amp\delay;

class TopService implements ServiceProvider
{
    public function __construct(private LoggerInterface $logger, private TopMiddleware $middleware)
    {
    }

    public function services(): array
    {
        return [
            'top',
        ];
    }

    /**
     * @return Promise<void>
     */
    public function top(CancellationToken $cancel): Promise
    {
        return call(function () use ($cancel) {
            while (true) {
                yield delay(1000);
                $requests = $this->middleware->requests();
                $this->logger->notice(sprintf(
                    'top: %s requests: %s',
                    array_sum($requests),
                    count($requests) === 0 ? 'n/a' : implode(', ', array_map(function (string $key, int $val) {
                        return sprintf('%s x %d', $key, $val);
                    }, array_keys($requests), array_values($requests)))
                ));
                if ($cancel->isRequested()) {
                    return;
                }
            }
        });
    }
}
