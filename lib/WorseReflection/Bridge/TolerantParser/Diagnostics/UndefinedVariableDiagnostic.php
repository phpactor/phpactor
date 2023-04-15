<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;

class UndefinedVariableDiagnostic implements Diagnostic
{
    /**
     * @param list<string> $suggestions
     */
    public function __construct(private ByteOffsetRange $byteOffsetRange, private string $varName, private array $suggestions)
    {
    }

    public function range(): ByteOffsetRange
    {
        return $this->byteOffsetRange;
    }

    public function severity(): DiagnosticSeverity
    {
        return DiagnosticSeverity::ERROR();
    }

    public function message(): string
    {
        if(count($this->suggestions) === 0) {
            return sprintf(
                'Undefined variable "$%s"',
                $this->varName
            );
        }
        return sprintf(
            'Undefined variable "$%s", did you mean one of "$%s"',
            $this->varName,
            implode('", "$', $this->suggestions)
        );
    }

}
