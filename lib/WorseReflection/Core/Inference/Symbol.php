<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\TextDocument\ByteOffsetRange;
use InvalidArgumentException;

final class Symbol
{
    public const CLASS_ = 'class';
    public const VARIABLE = 'variable';
    public const METHOD = 'method';
    public const FUNCTION = 'function';
    public const PROPERTY = 'property';
    public const CONSTANT = 'constant';
    public const DECLARED_CONSTANT = 'declared_constant';
    public const CASE = 'case';
    public const STRING = 'string';
    public const NUMBER = 'number';
    public const BOOLEAN = 'boolean';
    public const ARRAY = 'array';
    public const UNKNOWN = '<unknown>';

    private string $name;

    /**
     * @param Symbol::* $symbolType
     */
    private function __construct(private string $symbolType, string $name, private ByteOffsetRange $position)
    {
        $this->name = ltrim($name, '$');
    }

    public function __toString()
    {
        return sprintf('%s:%s [%s] %s', $this->position->start()->asInt(), $this->position->endAsInt(), $this->symbolType, $this->name);
    }

    public static function unknown(): Symbol
    {
        return new self(self::UNKNOWN, self::UNKNOWN, ByteOffsetRange::fromInts(0, 0));
    }

    public function isKnown(): bool
    {
        return $this->symbolType !== self::UNKNOWN;
    }

    /**
     * @return self::*
     */
    public static function castSymbolType(string $symbolType): string
    {
        if (false === in_array($symbolType, self::validSymbols())) {
            throw new InvalidArgumentException(sprintf(
                'Invalid symbol type "%s", valid symbol names: "%s"',
                $symbolType,
                implode('", "', self::validSymbols())
            ));
        }

        /** @phpstan-ignore-next-line */
        return $symbolType;
    }

    public static function fromTypeNameAndPosition(string $symbolType, string $name, ByteOffsetRange $position): Symbol
    {
        $symbolType = self::castSymbolType($symbolType);
        return new self($symbolType, $name, $position);
    }

    /**
     * @return Symbol::*
     */
    public function symbolType(): string
    {
        return $this->symbolType;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function position(): ByteOffsetRange
    {
        return $this->position;
    }

    /**
     * @param self::* $symbolType
     */
    public function withSymbolType(string $symbolType): self
    {
        return new self($symbolType, $this->name, $this->position);
    }

    public function withSymbolName(string $symbolName): self
    {
        return new self($this->symbolType, $symbolName, $this->position);
    }

    /**
     * @return array<array-key,self::*>
     */
    private static function validSymbols(): array
    {
        return [
            self::CLASS_,
            self::VARIABLE,
            self::UNKNOWN,
            self::PROPERTY,
            self::CONSTANT,
            self::FUNCTION,
            self::METHOD,
            self::STRING,
            self::NUMBER,
            self::BOOLEAN,
            self::ARRAY,
            self::CASE,
        ];
    }
}
