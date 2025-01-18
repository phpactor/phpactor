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
        if (count($this->suggestions) === 0) {
            return sprintf(
                'Undefined variable "$%s"',
                $this->varName
            );
        }
        $suggestString = implode('", "$', $this->suggestions);
        if (count($this->suggestions) === 1) {
            return sprintf(
                'Undefined variable "$%s", did you mean "$%s"',
                $this->varName,
                $suggestString
            );
        }
        return sprintf(
            'Undefined variable "$%s", did you mean one of "$%s"',
            $this->varName,
            $suggestString
        );
    }
    /**
     * @return list<string>
     */
    public function suggestions(): array
    {
        return $this->suggestions;
    }

    public function undefinedVariableName(): string
    {
        return $this->varName;
    }

    public function tags(): array
    {
        return [];
    }

    public function code(): string
    {
        return 'undefined_variable';
    }
}
