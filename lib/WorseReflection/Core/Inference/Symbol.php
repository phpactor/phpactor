<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Position;
use InvalidArgumentException;

final class Symbol
{
    const CLASS_ = 'class';

    const VARIABLE = 'variable';

    const METHOD = 'method';

    const FUNCTION = 'function';

    const PROPERTY = 'property';

    const CONSTANT = 'constant';

    const CASE = 'case';

    const STRING = 'string';

    const NUMBER = 'number';

    const BOOLEAN = 'boolean';

    const ARRAY = 'array';

    const UNKNOWN = '<unknown>';

    const VALID_SYMBOLS = [
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
    
    private string $symbolType;
    
    private string $name;
    
    private Position $position;

    private function __construct(string $symbolType, string $name, Position $position)
    {
        $this->symbolType = $symbolType;
        $this->name = ltrim($name, '$');
        $this->position = $position;
    }

    public function __toString()
    {
        return sprintf('%s:%s [%s] %s', $this->position->start(), $this->position->end(), $this->symbolType, $this->name);
    }

    public static function unknown(): Symbol
    {
        return new self('<unknown>', '<unknown>', Position::fromStartAndEnd(0, 0));
    }

    public static function assertValidSymbolType(string $symbolType): void
    {
        if (false === in_array($symbolType, self::VALID_SYMBOLS)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid symbol type "%s", valid symbol names: "%s"',
                $symbolType,
                implode('", "', self::VALID_SYMBOLS)
            ));
        }
    }

    public static function fromTypeNameAndPosition(string $symbolType, string $name, Position $position): Symbol
    {
        self::assertValidSymbolType($symbolType);
        return new self($symbolType, $name, $position);
    }

    public function symbolType(): string
    {
        return $this->symbolType;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function position(): Position
    {
        return $this->position;
    }
}
