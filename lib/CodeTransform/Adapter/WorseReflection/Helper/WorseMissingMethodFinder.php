<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Helper;

use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder\MissingMethod;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodDiagnostic;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;

class WorseMissingMethodFinder implements MissingMethodFinder
{
    private Reflector $reflector;

    public function __construct(Reflector $reflector, Parser $parser)
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
