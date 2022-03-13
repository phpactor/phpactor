<?php

namespace Phpactor\Extension\CodeTransformExtra\Application;

use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Filesystem\Domain\FilePath;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AbstractClassGenerator
{
    protected ClassFileNormalizer $normalizer;

    protected Generators $generators;

    private LoggerInterface $logger;

    public function __construct(ClassFileNormalizer $normalizer, Generators $generators, LoggerInterface $logger = null)
    {
        $this->normalizer = $normalizer;
        $this->generators = $generators;
        $this->logger = $logger ?: new NullLogger();
    }

    public function availableGenerators()
    {
        return $this->generators->names();
    }

    protected function logger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function writeFile(string $filePath, string $code, bool $overwrite): void
    {
        if (false === $overwrite && file_exists($filePath) && 0 !== filesize($filePath)) {
            throw new Exception\FileAlreadyExists(sprintf('File "%s" already exists and is non-empty', $filePath));
        }

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        file_put_contents(FilePath::fromString($filePath), (string) $code);
    }
}
