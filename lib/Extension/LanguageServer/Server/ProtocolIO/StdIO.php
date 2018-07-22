<?php

namespace Phpactor\Extension\LanguageServer\Server\ProtocolIO;

use Phpactor\Extension\LanguageServer\Server\ProtocolIO;
use Phpactor\Extension\LanguageServer\Server\StdOut;
use Psr\Log\LoggerInterface;
use RuntimeException;

class StdIO implements ProtocolIO
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function initialize(): void
    {
        stream_set_blocking(STDIN, false);
        $this->logger->info(sprintf('Listening on STDIN, PID: %s', getmypid()));
    }

    public function wait()
    {
    }

    public function readHeaders(): string
    {
        do {
            pcntl_signal_dispatch();
            $headers = fgets(STDIN);
            usleep(5000);
        } while ($headers === false);

        if (false === $headers) {
            throw new RuntimeException(
                'Could not read headers'
            );
        }
        $this->logger->debug('HEADERS: ' . $headers);

        return $headers;
    }

    public function send(string $response): void
    {
        $this->logger->debug('RESPONSE: ' . $response);
        fwrite(STDOUT, $response);
    }

    public function readPayload(int $length): string
    {
        $payload = fread(STDIN, $length);

        if (false === $payload) {
            throw new RuntimeException(sprintf(
                'Payload was null'
            ));
        }
        $this->logger->debug('PAYLOAD: ' . $payload);

        return $payload;
    }

    public function terminate()
    {
    }
}
