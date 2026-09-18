<?php

namespace Phpactor\Indexer\Adapter\Parallel;

/**
 * A frame is a single type character, the byte length of the payload
 * and a newline, followed by exactly that many bytes:
 *
 *     J42\n<42 bytes of payload>
 *
 * Payloads are serialized PHP or NULL separated paths, so they are not
 * newline safe and cannot be read a line at a time.
 */
final class Protocol
{
    public const FRAME_JOB = 'J';
    public const FRAME_RECORDS = 'R';
    public const PATH_SEPARATOR = "\0";

    public static function encode(string $type, string $payload): string
    {
        return $type . strlen($payload) . "\n" . $payload;
    }

    /**
     * Take as many whole frames as are available off the front of the buffer.
     *
     * @param-out string $buffer
     * @return list<array{string,string}> tuples of frame type and payload
     */
    public static function decode(string &$buffer): array
    {
        $frames = [];

        while (true) {
            $newline = strpos($buffer, "\n");

            if (false === $newline) {
                break;
            }

            $type = substr($buffer, 0, 1);
            $length = (int) substr($buffer, 1, $newline - 1);
            $end = $newline + 1 + $length;

            if (strlen($buffer) < $end) {
                break;
            }

            $frames[] = [$type, substr($buffer, $newline + 1, $length)];
            $buffer = substr($buffer, $end);
        }

        return $frames;
    }
}
