<?php

namespace Phpactor\Extension\LanguageServer\Transmitter;

use Phpactor\LanguageServer\Core\Rpc\Message;
use Phpactor\LanguageServer\Core\Server\Transmitter\MessageTransmitter;
use Psr\Log\LoggerInterface;

class TraceMessageTransmitter implements MessageTransmitter
{
    public function __construct(private MessageTransmitter $transmitter, private LoggerInterface $logger)
    {
    }

    public function transmit(Message $message): void
    {
        $this->transmitter->transmit($message);
        $encoded = json_encode($message);

        if (false === $encoded) {
            $encoded = '<could not encode request>';
        }

        $direction = '!!';

        $message = sprintf('TRAC %s %s', $direction, (function (string $value) {
            if (strlen($value) > 80) {
                return substr($value, 0, 79).'⋯';
            }
            return $value;
        })($encoded));

        $this->logger->info($message, (array)json_decode($encoded, true));
    }
}
