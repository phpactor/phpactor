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
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Reflector;

class WorseMissingMethodFinder implements MissingMethodFinder
{
    private Reflector $reflector;

    private Parser $parser;

    public function __construct(Reflector $reflector, Parser $parser)
    {
        $this->reflector = $reflector;
        $this->parser = $parser;
    }

    
    public function find(TextDocument $sourceCode): array
    {
        $node = $this->parser->parseSourceFile($sourceCode->__toString());
        $names = $this->methodNames($node);
        $missing = [];

        foreach ($names as $name) {
            $offset = $this->reflector->reflectOffset($sourceCode, $name->start);
            $containerType = $offset->symbolContext()->containerType();

            if (!$containerType->isDefined()) {
                continue;
            }

            try {
                $class = $this->reflector->reflectClassLike($containerType->__toString());
            } catch (NotFound $notFound) {
                continue;
            }

            $methodName = $name->getText($sourceCode->__toString());
            if (!is_string($methodName)) {
                continue;
            }
            try {
                $class->methods()->get($methodName);
            } catch (NotFound $notFound) {
                $missing[] = new MissingMethod(
                    $methodName,
                    ByteOffsetRange::fromInts($name->start, $name->start + $name->length)
                );
            }
        }

        return $missing;
    }

    /**
     * @return Token[]
     */
    private function methodNames(SourceFileNode $node): array
    {
        $names = [];
        foreach ($node->getDescendantNodes() as $node) {
            if ((!$node instanceof CallExpression)) {
                continue;
            }

            assert($node instanceof CallExpression);

            $memberName = null;
            if ($node->callableExpression instanceof MemberAccessExpression) {
                $memberName = $node->callableExpression->memberName;
            } elseif ($node->callableExpression instanceof ScopedPropertyAccessExpression) {
                $memberName = $node->callableExpression->memberName;
            }

            if (!($memberName instanceof Token)) {
                continue;
            }

            $names[] = $memberName;
        }

        return $names;
    }
}
