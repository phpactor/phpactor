<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class UnresolvableNameDiagnostic implements Diagnostic
{
    private const TYPE_CLASS = 'class';
    private const TYPE_FUNCTION = 'function';

    private ByteOffsetRange $range;

    /**
     * @var self::TYPE_*
     */
    private string $type;

    private string $name;

    /**
     * @param self::TYPE_* $type
     */
    private function __construct(
        ByteOffsetRange $range,
        string $type,
        string $name
    )
    {
        $this->range = $range;
        $this->type = $type;
        $this->name = $name;
    }

    public static function forFunction(ByteOffsetRange $range, string $name): self
    {
        return new self($range, self::TYPE_FUNCTION, $name);
    }

    public static function forClass(ByteOffsetRange $range, string $name): self
    {
        return new self($range, self::TYPE_CLASS, $name);
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): DiagnosticSeverity
    {
        return DiagnosticSeverity::ERROR();
    }

    public function message(): string
    {
        return sprintf('%s "%s" not found', ucfirst($this->type), $this->name);
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }
}
