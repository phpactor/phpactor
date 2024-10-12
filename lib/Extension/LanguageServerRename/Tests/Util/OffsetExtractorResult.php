<?php

namespace Phpactor\Extension\LanguageServerRename\Tests\Util;

use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\ByteOffsetRange;
use RuntimeException;
use function array_reduce;

final class OffsetExtractorResult
{
    /**
     * @param array<string, ByteOffset[]> $offsets
     * @param array<string, ByteOffsetRange[]> $ranges
     */
    public function __construct(private string $source, private array $offsets, private array $ranges)
    {
    }

    public function source(): string
    {
        return $this->source;
    }

    /**
     * @return ByteOffset[]
     */
    public function offsets(?string $name = null): array
    {
        if (null === $name) {
            return array_reduce($this->offsets, function (array $carry, array $offsets) {
                return array_merge($carry, $offsets);
            }, []);
        }

        if (!isset($this->offsets[$name])) {
            throw new RuntimeException(sprintf(
                'No offset registered with name "%s", known names "%s"',
                $name,
                implode('", "', array_keys($this->offsets))
            ));
        }

        return $this->offsets[$name];
    }

    public function offset(?string $name = null): ByteOffset
    {
        $offsets = $this->offsets($name);

        if (!count($offsets)) {
            throw new RuntimeException(sprintf(
                'No "%s" offsets found in source code',
                $name
            ));
        }

        $offset = reset($offsets);

        return $offset;
    }

    /**
     * @return ByteOffsetRange[]
     */
    public function ranges(?string $name = null): array
    {
        if (null === $name) {
            return array_reduce($this->ranges, function (array $carry, array $ranges) {
                return array_merge($carry, $ranges);
            }, []);
        }

        if (!isset($this->ranges[$name])) {
            throw new RuntimeException(sprintf(
                'No range registered with name "%s", known names "%s"',
                $name,
                implode('", "', array_keys($this->ranges))
            ));
        }

        return $this->ranges[$name];
    }

    public function range(?string $name = null): ByteOffsetRange
    {
        $ranges = $this->ranges($name);

        if (!count($ranges)) {
            throw new RuntimeException(sprintf(
                'No "%s" ranges found in source code',
                $name
            ));
        }

        $range = reset($ranges);

        return $range;
    }
}
