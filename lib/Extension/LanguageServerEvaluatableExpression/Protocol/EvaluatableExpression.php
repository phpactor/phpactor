<?php

namespace Phpactor\Extension\LanguageServerEvaluatableExpression\Protocol;

use RuntimeException;
use Phpactor\LanguageServerProtocol\Range;

class EvaluatableExpression
{
    public ?string $expression;

    public Range $range;

    public function __construct(Range $range, ?string $expression = null)
    {
        $this->expression = $expression;
        $this->range = $range;
    }

    /**
     * @param array<string,mixed> $array
     * @return self
     */
    public static function fromArray(array $array, bool $allowUnknownKeys = false)
    {
        if (!is_array($array['range'])) {
            throw new RuntimeException('Missing "range"');
        }
        $range = Range::fromArray($array['range']);
        $expression = is_string($array['expression']) ? $array['expression'] : null;
        return new self($range, $expression);
    }

}
