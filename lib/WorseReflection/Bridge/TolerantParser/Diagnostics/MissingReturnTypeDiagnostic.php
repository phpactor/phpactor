<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Type;

class MissingReturnTypeDiagnostic implements Diagnostic
{
    private ByteOffsetRange $range;

    private string $classType;

    private string $methodName;

    private Type $returnType;

    public function __construct(
        ByteOffsetRange $range,
        string $classType,
        string $methodName,
        Type $returnType
    ) {
        $this->range = $range;
        $this->classType = $classType;
        $this->methodName = $methodName;
        $this->returnType = $returnType;
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
        if (!$this->returnType->isDefined()) {
            return sprintf(
                'Method "%s" is missing return type and the type could not be determined',
                $this->methodName
            );
        }
        return sprintf(
            'Missing return type `%s`',
            $this->returnType->toPhpString(),
        );
    }

    public function classType(): string
    {
        return $this->classType;
    }

    public function methodName(): string
    {
        return $this->methodName;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }
}
