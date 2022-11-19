<?php

namespace Phpactor\FilePathResolver;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

class LoggingPathResolver implements PathResolver
{
    public function __construct(
        private PathResolver $pathResolver,
        private LoggerInterface $logger,
        private string $level = LogLevel::DEBUG
    ) {
    }

    public function resolve(string $path): string
    {
        $resolvedPath = $this->pathResolver->resolve($path);
        $this->logger->log(
            $this->level,
            sprintf(
                'Resolved path "%s" to "%s"',
                $path,
                $resolvedPath
            )
        );

        return $resolvedPath;
    }
}
