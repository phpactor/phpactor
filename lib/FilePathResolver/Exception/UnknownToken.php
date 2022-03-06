<?php

namespace Phpactor\FilePathResolver\Exception;

use RuntimeException;

class UnknownToken extends RuntimeException
{
    public function __construct(string $tokenName, array $knownTokens)
    {
        parent::__construct(sprintf(
            'Unknown token "%s", known tokens: "%s"',
            $tokenName,
            implode('", "', $knownTokens)
        ));
    }
}
