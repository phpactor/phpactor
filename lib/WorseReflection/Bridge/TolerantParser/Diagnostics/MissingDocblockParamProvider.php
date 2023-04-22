<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use PHPUnit\Framework\Assert;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MissingDocblockParamProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MethodDeclaration) {
            return;
        }

        $declaration = NodeUtil::nodeContainerClassLikeDeclaration($node);

        if (null === $declaration) {
            return;
        }

        try {
            $class = $resolver->reflector()->reflectClassLike($declaration->getNamespacedName()->__toString());
            $methodName = $node->name->getText($node->getFileContents());
            if (!is_string($methodName)) {
                return;
            }
            $method = $class->methods()->get($methodName);
        } catch (NotFound) {
            return;
        }

        // do not try it for overriden methods
        if ($method->original()->declaringClass()->name() != $class->name()) {
            return;
        }

        $docblock = $method->docblock();
        $docblockParams = $docblock->params();
        $missingParams = [];

        foreach ($method->parameters() as $parameter) {
            $type = $parameter->type();
            $type = $this->upcastType($type, $resolver);
            $parameterType = $parameter->type();

            if ($docblockParams->has($parameter->name())) {
                continue;
            }

            if ($parameter->isVariadic()) {
                if ($type instanceof ArrayType) {
                    $type = $type->iterableValueType();
                }
                if ($parameterType instanceof ArrayType) {
                    $parameterType = $parameterType->iterableValueType();
                }
            }

            if ($type instanceof ArrayType) {
                $type = new ArrayType(TypeFactory::int(), TypeFactory::mixed());
            }

            // replace <undefined> with "mixed"
            $type = $type->map(fn (Type $type) => $type instanceof MissingType ? new MixedType() : $type);

            if ($type->__toString() === $parameterType->__toString()) {
                continue;
            }

            yield new MissingDocblockParamDiagnostic(
                ByteOffsetRange::fromInts(
                    $parameter->position()->start()->toInt(),
                    $parameter->position()->end()->toInt()
                ),
                sprintf(
                    'Method "%s" is missing @param $%s',
                    $methodName,
                    $parameter->name(),
                ),
                DiagnosticSeverity::WARNING(),
                $class->name()->__toString(),
                $methodName,
                $parameter->name(),
                $type,
            );
        }
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'reports a missing docblock param on closure',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public function foo(Closure $foobar) {
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                $diagnostics = $diagnostics->byClass(MissingDocblockParamDiagnostic::class);
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "foo" is missing @param $foobar', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'reports a missing docblock param on generator',
            source: <<<'PHP'
                <?php

                /**
                 * @template TKey
                 * @template TValue of string
                 */
                class Generator {
                }

                class Foobar
                {
                    public function foo(Generator $foobar) {
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                $diagnostics = $diagnostics->byClass(MissingDocblockParamDiagnostic::class);
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "foo" is missing @param $foobar', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'reports a missing docblock on array',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public function foo(array $foobar) {
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                $diagnostics = $diagnostics->byClass(MissingDocblockParamDiagnostic::class);
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "foo" is missing @param $foobar', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'does not report diagnostic on method with @param',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    /**
                     * @param string[] $foobar  
                     */
                    public function foo(array $foobar): array {
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                $diagnostics = $diagnostics->byClass(MissingDocblockParamDiagnostic::class);
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'report diagnostic on on variadic',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public function foo(string ...$foobars) {
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
    }

    private function upcastType(Type $type, NodeContextResolver $resolver): Type
    {
        if (!$type instanceof ReflectedClassType) {
            return $type;
        }

        if ($type->name()->__toString() === 'Closure') {
            return new ClosureType($resolver->reflector(), [], TypeFactory::void());
        }

        return $type->upcastToGeneric();
    }
}
