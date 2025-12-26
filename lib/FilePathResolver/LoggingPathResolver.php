<?php

namespace Phpactor\FilePathResolver;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

class LoggingPathResolver implements PathResolver
{
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly LoggerInterface $logger,
        private readonly string $level = LogLevel::DEBUG
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
