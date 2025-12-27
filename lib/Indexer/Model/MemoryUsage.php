<?php

namespace Phpactor\Indexer\Model;

use RuntimeException;
use function ini_get;
use function memory_get_usage;

final class MemoryUsage
{
    private function __construct(
        private readonly ?int $memoryLimit,
        private readonly int $memoryUsage,
        private readonly int $precision = 0
    ) {
    }

    public static function create(): self
    {
        return new self(self::parseMemoryLimit((string)ini_get('memory_limit')), memory_get_usage(true));
    }

    public static function createFromLimitAndUsage(string $limit, int $usage): self
    {
        return new self(self::parseMemoryLimit($limit), $usage);
    }

    public function memoryUsageFormatted(): string
    {
        return sprintf('%s/%s mb', $this->formatMemory($this->memoryUsage), $this->formatMemory($this->memoryLimit));
    }

    public function memoryLimit(): ?int
    {
        return $this->memoryLimit;
    }

    public function memoryUsage(): int
    {
        return $this->memoryUsage;
    }

    private function formatMemory(?int $nbBytes): string
    {
        if (null === $nbBytes) {
            return '∞';
        }

        return number_format($nbBytes / 1000 / 1000, $this->precision);
    }

    private static function parseMemoryLimit(string $limit): ?int
    {
        if ($limit === '-1') {
            return null;
        }

        if (is_numeric($limit)) {
            return (int)$limit;
        }

        if (strlen($limit) < 2) {
            throw new RuntimeException(sprintf(
                'Invalid memory limit "%s"',
                $limit
            ));
        }


        $unit = substr($limit, -1, 1);
        $amount = (int)substr($limit, 0, -1);

        if ($unit === 'K') {
            return $amount * 1000;
        }

        if ($unit === 'M') {
            return $amount * 1000 * 1000;
        }

        if ($unit === 'G') {
            return $amount * 1000 * 1000 * 1000;
        }

        return null;
    }
}
