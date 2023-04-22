<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use PHPUnit\Framework\Assert;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MissingReturnTypeProvider implements DiagnosticProvider
{
    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'reports missing return type',
            source: <<<'PHP'
                <?php

                class Foobar {
                    public function foo()
                    {
                        return 'string';
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Missing return type `string`',
                    $diagnostics->at(0)->message()
                );
            }
        );
        yield new DiagnosticExample(
            title: 'does not report missing return type on _construct',
            source: <<<'PHP'
                <?php

                class Foobar {
                    public function __construct()
                    {
                    }

                    public function __destruct()
                    {
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'missing return type with missing type',
            source: <<<'PHP'
                <?php

                class Foobar {
                    public function foo()
                    {
                        return foo();
                    }
                }

                function foo() {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Method "foo" is missing return type and the type could not be determined',
                    $diagnostics->at(0)->message()
                );
            }
        );
    }
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MethodDeclaration) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($node, $node->name);

        if (!$methodName) {
            return;
        }

        if ($node->returnTypeList) {
            return;
        }

        $type = $resolver->resolveNode($frame, $node)->containerType();

        if (!$type instanceof ReflectedClassType) {
            return;
        }

        $reflection = $type->reflectionOrNull();

        if (!$reflection) {
            return;
        }

        // if it's an interface we can't determine the return type
        if ($reflection instanceof ReflectionInterface) {
            return;
        }

        $methods = $reflection->methods()->belongingTo($reflection->name())->byName($methodName);

        if (0 === count($methods)) {
            return;
        }

        $method = $methods->first();

        if ($method->isAbstract()) {
            return;
        }

        if ($method->name() === '__construct') {
            return;
        }

        if ($method->name() === '__destruct') {
            return;
        }

        if ($method->type()->isDefined()) {
            return;
        }

        if ($method->docblock()->returnType()->isMixed()) {
            return;
        }

        if ($method->class()->templateMap()->has($method->docblock()->returnType()->__toString())) {
            return;
        }

        $returnType = $frame->returnType();

        yield new MissingReturnTypeDiagnostic(
            $method->nameRange(),
            $reflection->name()->__toString(),
            $methodName,
            $returnType->generalize()->reduce()
        );
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
