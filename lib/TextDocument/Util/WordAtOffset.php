<?php

namespace Phpactor\TextDocument\Util;

use OutOfBoundsException;
use RuntimeException;

final class WordAtOffset
{
    const SPLIT_WORD = '\s|;|\\\|%|\(|\)|\[|\]|:|\r|\r\n|\n';

    // see https://www.php.net/manual/en/language.oop5.basic.php
    const SPLIT_PHP_NAME = '[^a-zA-Z0-9_\x80-\xff]';
    const SPLIT_QUALIFIED_PHP_NAME = '[^a-zA-Z0-9_\x80-\xff\\\]';

    public function __construct(private readonly string $splitPattern = self::SPLIT_WORD)
    {
    }

    public function __invoke(string $text, int $byteOffset): string
    {
        $chunks = preg_split('{(' . $this->splitPattern . ')}', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if (false === $chunks) {
            throw new RuntimeException(
                'Failed to preg-split text into chunks'
            );
        }

        $start = 1;
        foreach ($chunks as $chunk) {
            $end = $start + strlen($chunk);
            if ($byteOffset >= $start && $byteOffset < $end) {
                return $chunk;
            }
            $start = $end;
        }

        throw new OutOfBoundsException(sprintf(
            'Byte offset %s is larger than text length %s',
            $byteOffset,
            strlen($text)
        ));
    }
}
