<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Deprecation;

class DeprecatedUsageDiagnostic implements Diagnostic
{
    public function __construct(
        private ByteOffsetRange $range,
        private string $memberName,
        private Deprecation $deprecation,
        private string $memberType
    ) {
    }

    public function range(): ByteOffsetRange
    {
        return $this->range;
    }

    public function severity(): DiagnosticSeverity
    {
        return DiagnosticSeverity::WARNING();
    }

    public function message(): string
    {
        $message = sprintf('Call to deprecated %s "%s"', $this->memberType, $this->memberName);

        if ($this->deprecation->message()) {
            $message .= sprintf(': %s', $this->deprecation->message());
        }

        if($this->deprecation->replacementSuggestion()) {
            $message .= ' (see: '.$this->deprecation->replacementSuggestion().')';
        }

        return $message;
    }
}
