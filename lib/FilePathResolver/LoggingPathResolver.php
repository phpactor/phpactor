<?php

namespace Phpactor\FilePathResolver;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;

class LoggingPathResolver implements PathResolver
{
    private LoggerInterface $logger;

    private PathResolver $pathResolver;

    private string $level;

    public function __construct(PathResolver $pathResolver, LoggerInterface $logger, string $level = LogLevel::DEBUG)
    {
        $this->logger = $logger;
        $this->pathResolver = $pathResolver;
        $this->level = $level;
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
