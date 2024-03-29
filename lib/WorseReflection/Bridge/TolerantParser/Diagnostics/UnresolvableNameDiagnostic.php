<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\Name\Name;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\DiagnosticRelatedInformation;

class UnresolvableNameDiagnostic implements Diagnostic
{
    public const TYPE_CLASS = 'class';
    public const TYPE_FUNCTION = 'function';

    /**
     * @param self::TYPE_* $type
     */
    private function __construct(private ByteOffsetRange $range, private string $type, private Name $name)
    {
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

    public function relatedInformation(): ?DiagnosticRelatedInformation
    {
        return null;
    }
}
