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
class DocblockMissingImplementsTagProvider implements DiagnosticProvider
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
                interface NeedGeneric
                {
                }

                class Foobar implements NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Missing generic tag `@implements NeedGeneric<mixed>`',
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
                interface NeedGeneric
                {
                }

                /**
                 * @extends NeedGeneric<int>
                 */
                class Foobar implements NeedGeneric
                {
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals(
                    'Generic tag `@implements NeedGeneric<int>` should be compatible with `@implements NeedGeneric<mixed,mixed>`',
                    $diagnostics->at(0)->message()
                );
            }
        );
    }

    public function name(): string
    {
        return 'docblock_missing_extends_tag';
    }
}
