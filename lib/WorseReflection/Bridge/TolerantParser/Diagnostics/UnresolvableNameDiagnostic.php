<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\Name\Name;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class UnresolvableNameDiagnostic implements Diagnostic
{
    public const TYPE_CLASS = 'class';
    public const TYPE_FUNCTION = 'function';

    /**
     * @param self::TYPE_* $type
     */
    private function __construct(
        private readonly ByteOffsetRange $range,
        private readonly string $type,
        private readonly Name $name
    ) {
    }

    public static function forFunction(ByteOffsetRange $range, Name $name): self
    {
        return new self($range, self::TYPE_FUNCTION, $name);
    }

    public static function forClass(ByteOffsetRange $range, Name $name): self
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
        return sprintf('%s "%s" not found', ucfirst($this->type), $this->name->head()->__toString());
    }

    /**
     * @return self::TYPE_*
     */
    public function type(): string
    {
        return $this->type;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function tags(): array
    {
        return [];
    }

    public function code(): string
    {
        return 'unresolved_name';
    }
}
