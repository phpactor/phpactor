<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodDiagnostic;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class WorseMissingMethodFinder implements MissingMethodFinder
{
    public function __construct(private Reflector $reflector)
    {
    }


    public function find(TextDocument $sourceCode): Promise<array>
    {
        return call(function () use ($sourceCode) {
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
        });
    }
}
