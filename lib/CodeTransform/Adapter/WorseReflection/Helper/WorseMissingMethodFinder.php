<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodDiagnostic;
use Phpactor\WorseReflection\Reflector;

class WorseMissingMethodFinder implements MissingMethodFinder
{
    private Reflector $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }


    public function find(TextDocument $sourceCode): array
    {
        $diagnostics = $this->reflector->diagnostics($sourceCode)->byClass(MissingMethodDiagnostic::class);
        $missing = [];

        /** @var MissingMethodDiagnostic $missingMethod */
        foreach ($diagnostics as $missingMethod) {
            $missing[] = new MissingMethod(
                $missingMethod->methodName(),
                $missingMethod->range(),
            );
        }

        return $missing;
    }
}
