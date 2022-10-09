<?php

namespace Phpactor\WorseReflection\Core\SourceCodeLocator;

use Phpactor\WorseReflection\Core\Exception\SourceNotFound;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator;

/**
 * Quick and permanent mapping for stubs for which WR as definite expectations.
 */
final class InternalLocator implements SourceCodeLocator
{
    /**
     * @var array<string,string>
     */
    private array $map;

    /**
     * @param array<string,string> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public static function forInternalStubs(): self
    {
        return new self([
            'iterable' => __DIR__ . '/InternalStubs/Iterator.php',
            'Traversable' => __DIR__ . '/InternalStubs/Iterator.php',
            'IteratorAggregate' => __DIR__ . '/InternalStubs/Iterator.php',
            'Iterator' => __DIR__ . '/InternalStubs/Iterator.php',
            'UnitEnumCase' => __DIR__ . '/InternalStubs/Enum.php',
            'UnitEnum' => __DIR__ . '/InternalStubs/Enum.php',
            'BackedEnumCase' => __DIR__ . '/InternalStubs/Enum.php',
            'BackedEnum' => __DIR__ . '/InternalStubs/Enum.php',
        ]);
    }

    public function locate(Name $name): SourceCode
    {
        if (isset($this->map[$name->__toString()])) {
            return SourceCode::fromPath($this->map[$name->__toString()]);
        }
        throw new SourceNotFound(sprintf(
            'Could not find internal stub for "%s"',
            (string) $name
        ));
    }
}
