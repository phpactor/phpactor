<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use PHPUnit\Framework\Assert;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * Report when a class or interface depends on an class or interface that
 * requires generic annotations.
 */
class MissingDocblockClassGenericProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof ClassDeclaration) {
            return;
        }

        if (!$node->name) {
            return;
        }

        $range = ByteOffsetRange::fromInts(
            $node->name->getStartPosition(),
            $node->name->getEndPosition()
        );

        try {
            $class = $resolver->reflector()->reflectClassLike($node->getNamespacedName()->__toString());
        } catch (NotFound) {
            return;
        }

        if ($class instanceof ReflectionClass) {
            yield from $this->fromReflectionClass($resolver->reflector(), $range, $class, $class->parent());
        }
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'extends class requiring generic annotation',
            source: <<<'PHP'
                <?php

                /**
                 * @template T
                 */
                class NeedGeneric
                {
                }

                class Foobar extends NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Missing generic docblock for class "Foobar": @extends NeedGeneric<mixed>',
                    $diagnostics->at(0)->message()
                );
            }
        );
        yield new DiagnosticExample(
            title: 'does not provide enough arguments',
            source: <<<'PHP'
                <?php

                /**
                 * @template T
                 * @template P
                 */
                class NeedGeneric
                {
                }

                /**
                 * @extends NeedGeneric<int>
                 */
                class Foobar extends NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Generic tag `@extends NeedGeneric<int>` should be compatible with `@extends NeedGeneric<mixed,mixed>`',
                    $diagnostics->at(0)->message()
                );
            }
        );
        yield new DiagnosticExample(
            title: 'does not provide any arguments',
            source: <<<'PHP'
                <?php

                /**
                 * @template T of int
                 */
                class NeedGeneric
                {
                }

                /**
                 * @extends NeedGeneric
                 */
                class Foobar extends NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Generic tag `@extends NeedGeneric` should be compatible with `@extends NeedGeneric<int>`',
                    $diagnostics->at(0)->message()
                );
            }
        );
        yield new DiagnosticExample(
            title: 'does not provide multiple arguments',
            source: <<<'PHP'
                <?php

                /**
                 * @template T
                 * @template P
                 * @template Q
                 */
                class NeedGeneric
                {
                }

                /**
                 * @extends NeedGeneric<int>
                 */
                class Foobar extends NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Generic tag `@extends NeedGeneric<int>` should be compatible with `@extends NeedGeneric<mixed,mixed,mixed>`',
                    $diagnostics->at(0)->message()
                );
            }
        );
        yield new DiagnosticExample(
            title: 'extends class not requiring generic annotation',
            source: <<<'PHP'
                <?php

                class NeedGeneric
                {
                }

                class Foobar extends NeedGeneric
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'provides extends class and provides annotation',
            source: <<<'PHP'
                <?php

                /**
                 * @template T
                 */
                class NeedGeneric
                {
                }

                /**
                 * @extends NeedGeneric<int>
                 */
                class Foobar extends NeedGeneric
                {
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
        return 'missing_phpdoc_return';
    }
    /**
     * @return Generator<MissingDocblockClassGenericDiagnostic>
     */
    private function fromReflectionClass(ClassReflector $reflector, ByteOffsetRange $range, ReflectionClass $class, ?ReflectionClass $parentClass): Generator
    {
        if (!$parentClass) {
            return;
        }

        $templateMap = $parentClass->templateMap();

        if (!count($templateMap)) {
            return;
        }

        $extends = $class->docblock()->extends();
        $extends = array_filter($extends, fn (Type $type) => $type->instanceof($parentClass->type()));
        $defaultGenericType = new GenericClassType(
            $reflector,
            $parentClass->name(),
            array_map(
                fn (Type $type) => !$type instanceof MissingType ?: new MixedType(),
                $templateMap->toArguments(),
            )
        );

        if (0 === count($extends)) {
            yield new MissingDocblockClassGenericDiagnostic(
                $range,
                $class->name(),
                $parentClass->name(),
                $defaultGenericType
            );
            return;
        }

        $extendTagType = $extends[0];
        if (!$extendTagType instanceof GenericClassType) {
            return;
        }

        if ($defaultGenericType->accepts($extendTagType)->isTrue()) {
            return;
        }

        yield new IncorrectDocblockClassGenericDiagnostic(
            $range,
            $extendTagType,
            $defaultGenericType
        );
    }
}
