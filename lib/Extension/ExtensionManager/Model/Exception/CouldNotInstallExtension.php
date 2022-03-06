<?php

namespace Phpactor\Extension\ExtensionManager\Model\Exception;

use Throwable;
use RuntimeException;

class CouldNotInstallExtension extends RuntimeException
{
    public function __construct(string $message, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
