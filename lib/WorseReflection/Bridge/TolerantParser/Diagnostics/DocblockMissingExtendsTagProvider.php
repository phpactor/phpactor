<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use PHPUnit\Framework\Assert;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\Docblock\ClassGenericDiagnosticHelper;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

/**
 * Report when a class extends a generic class but does not provide an @extends tag.
 */
class DocblockMissingExtendsTagProvider implements DiagnosticProvider
{
    private ClassGenericDiagnosticHelper $helper;

    public function __construct(?ClassGenericDiagnosticHelper $helper = null)
    {
        $this->helper = $helper ?: new ClassGenericDiagnosticHelper();
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof ClassDeclaration) {
            return;
        }

        /** @phpstan-ignore-next-line */
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
            yield from $this->helper->diagnosticsForExtends($resolver->reflector(), $range, $class, $class->parent());
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
                    'Missing generic tag `@extends NeedGeneric<mixed>`',
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
            title: 'provides empty arguments',
            source: <<<'PHP'
                <?php

                /**
                 * @template T of int
                 */
                class NeedGeneric
                {
                }

                /**
                 * @extends NeedGeneric<>
                 */
                class Foobar extends NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Missing generic tag `@extends NeedGeneric<int>`',
                    $diagnostics->at(0)->message()
                );
            }
        );
        yield new DiagnosticExample(
            title: 'wrong class',
            source: <<<'PHP'
                <?php

                /**
                 * @template T of int
                 */
                class NeedGeneric
                {
                }

                /**
                 * @extends NeedGeneic<int>
                 */
                class Foobar extends NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Missing generic tag `@extends NeedGeneric<int>`',
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

        yield new DiagnosticExample(
            title: 'considers the namespace',
            source: <<<'PHP'
                <?php

                namespace Phpactor;

                abstract class Model
                {
                }

                /**
                 * @template Model of \Phpactor\Model
                 */
                class Factory
                {
                }

                /**
                 * @extends Factory<Schedule>
                 */
                class ScheduleFactory extends Factory
                {
                }

                class Schedule extends Model
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'extend with typed templated argument',
            source: <<<'PHP'
                <?php

                abstract class Model {}
                class Schedule extends Model {}

                /** @template TModel of Model */
                class Factory {}

                /**
                 * @template TModel of Schedule
                 * @extends Factory<TModel>
                 */
                class ScheduleFactory extends Factory {}

                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'extend with unconstrained argument',
            source: <<<'PHP'
                <?php

                abstract class Model {}
                class Schedule extends Model {}

                /** @template TModel */
                class Factory {}

                /**
                 * @template TModel
                 * @extends Factory<TModel>
                 */
                class ScheduleFactory extends Factory {}

                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
    }
}
