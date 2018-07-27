<?php

namespace Phpactor\Extension\LanguageServer\Exception;

use RuntimeException;

class UnknownMethod extends RuntimeException implements LanguageServerException
{
    public function __construct(string $methodName, array $knownMethods)
    {
        parent::__construct(sprintf(
            'Method "%s" is not known, known methods: "%s"',
            $methodName,
            implode('", "', $knownMethods)
        ));
    }
}
