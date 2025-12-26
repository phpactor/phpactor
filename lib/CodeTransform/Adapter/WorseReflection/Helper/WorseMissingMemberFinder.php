<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Helper\MissingMemberFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMemberFinder\MissingMember;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMemberDiagnostic;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class WorseMissingMemberFinder implements MissingMemberFinder
{
    public function __construct(private readonly Reflector $reflector)
    {
    }


    public function find(TextDocument $sourceCode): Promise
    {
        return call(function () use ($sourceCode) {
            $diagnostics = (yield $this->reflector->diagnostics($sourceCode))->byClass(MissingMemberDiagnostic::class);
            $missing = [];

            /** @var MissingMemberDiagnostic $missingMethod */
            foreach ($diagnostics as $missingMethod) {
                $missing[] = new MissingMember(
                    $missingMethod->methodName(),
                    $missingMethod->range(),
                    $missingMethod->memberType(),
                );
            }

            return $missing;
        });
    }
}
