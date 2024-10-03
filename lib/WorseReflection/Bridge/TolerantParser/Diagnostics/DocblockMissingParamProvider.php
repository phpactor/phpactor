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
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\PseudoIterableType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * Report when a method has a parameter with a type that should be
 * augmented by a docblock tag.
 */
class DocblockMissingParamProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MethodDeclaration) {
            return;
        }

        if (!$node->name) {
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

        // do not try it for overridden methods
        if ($method->original()->declaringClass()->name() != $class->name()) {
            return;
        }

        $docblock = $method->docblock();
        $docblockParams = $docblock->params();
        $docblockVars = new DocBlockVars([]);
        $missingParams = [];

        foreach ($method->parameters() as $parameter) {
            $type = $parameter->type();
            $type = $this->upcastType($type, $resolver);
            $parameterType = $parameter->type();

            if ($docblockParams->has($parameter->name())) {
                continue;
            }

            if ($method->name() === '__construct') {
                $vars = $parameter->docblock()->vars();
                if ($vars->count() > 0) {
                    continue;
                }
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
            if ($type::class === PseudoIterableType::class) {
                $type = new PseudoIterableType(TypeFactory::int(), TypeFactory::mixed());
            }

            // replace <undefined> with "mixed"
            $type = $type->map(fn (Type $type) => $type instanceof MissingType ? new MixedType() : $type);

            if ($type->__toString() === $parameterType->__toString()) {
                continue;
            }

            yield new DocblockMissingParamDiagnostic(
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
            title: 'closure',
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
                $diagnostics = $diagnostics->byClass(DocblockMissingParamDiagnostic::class);
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "foo" is missing @param $foobar', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'generator',
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
                $diagnostics = $diagnostics->byClass(DocblockMissingParamDiagnostic::class);
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "foo" is missing @param $foobar', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'iterable',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public function foo(iterable $foobar) {
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                $diagnostics = $diagnostics->byClass(DocblockMissingParamDiagnostic::class);
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "foo" is missing @param $foobar', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'array',
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
                $diagnostics = $diagnostics->byClass(DocblockMissingParamDiagnostic::class);
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Method "foo" is missing @param $foobar', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'no false positive for union of scalars',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    /**
                     * @param array<'GET'|'POST'> $foobar
                     */
                    public function foo(array $foobar) {
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'no false positive array shape with string literals',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    /**
                     * @param array{foo: 'foo', bar: 'bar'} $foobar
                     */
                    public function foo(array $foobar) {
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'no false positive for vardoc on promoted property',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public function __construct(
                        /**
                         * @var array<'GET'|'POST'>
                         */
                        private array $foobar,
                        private array $barfoo
                    ) {
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
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
                $diagnostics = $diagnostics->byClass(DocblockMissingParamDiagnostic::class);
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'variadic',
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

    public function name(): string
    {
        return 'docblock_missing_param';
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
