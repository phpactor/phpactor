<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Node\NamespaceUseGroupClause;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Token;
use PHPUnit\Framework\Assert;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\AggregateType;

/**
 * Report if a use statement is not required.
 */
class UnusedImportProvider implements DiagnosticProvider
{
    /**
     * @var array<string,bool>
     */
    private array $usedPrefixes = [];

    /**
     * @var array<string,Node|Token>
     */
    private array $imported = [];

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        $docblock = $resolver->docblockFactory()->create(
            $node->getLeadingCommentAndWhitespaceText(),
            new ReflectionScope($resolver->reflector(), $node)
        );

        $this->extractDocblockNames($docblock, $resolver, $node);

        if ($node instanceof QualifiedName && !$node->parent instanceof NamespaceUseClause && !$node->parent instanceof NamespaceDefinition && !$node->parent instanceof NamespaceUseGroupClause) {
            $prefix = $node->getNameParts()[0];
            if (!$prefix instanceof Token) {
                return [];
            }
            $usedPrefix = $this->prefixedName($node, (string)$prefix->getText($node->getFileContents()));
            $this->usedPrefixes[$usedPrefix] = true;
            return [];
        }

        if ($node instanceof NamespaceUseClause) {
            if ($node->groupClauses) {
                foreach ($node->groupClauses->children as $groupClause) {
                    if (!$groupClause instanceof NamespaceUseGroupClause) {
                        continue;
                    }
                    $useClause = $groupClause->parent->parent;
                    if (!$useClause instanceof NamespaceUseClause) {
                        continue;
                    }

                    $this->imported[$this->prefixedName($groupClause, $groupClause->__toString())] = $groupClause;
                }
                return [];
            }

            $prefix = (function (Node $clause): string {
                /** @phpstan-ignore-next-line TP lies */
                if ($clause->namespaceAliasingClause) {
                    return $this->prefixedName($clause, (string)$clause->namespaceAliasingClause->name->getText($clause->getFileContents()));
                }
                /** @phpstan-ignore-next-line TP lies */
                $lastPart = $this->lastPart((string)$clause->namespaceName);
                return $this->prefixedName($clause, $lastPart);
            })($node);

            $this->imported[$prefix] = $node;
            return [];
        }

        return [];
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof SourceFileNode) {
            return [];
        }

        $contents = $node->getFileContents();

        foreach ($this->imported as $importedName => $imported) {
            if (isset($this->usedPrefixes[$importedName])) {
                continue;
            }

            // see if the imported name is used by an annotation
            if ($this->usedByAnnotation($contents, $importedName, $imported)) {
                continue;
            }

            // scan all usages and check if imported name is used relatively
            foreach (array_keys($this->usedPrefixes) as $prefix) {
                [$importedNamespace, $importedIdentifierName] = explode(':', $importedName);
                [$usedNamespace, $usedName] = explode(':', $prefix);

                if ($importedNamespace !== $usedNamespace) {
                    continue;
                }

                if (str_starts_with($usedName, $importedIdentifierName . '\\')) {
                    continue 2;
                }

                if (str_ends_with($usedName, $importedIdentifierName)) {
                    continue 2;
                }
            }

            yield UnusedImportDiagnostic::for(
                ByteOffsetRange::fromInts($imported->getStartPosition(), $imported->getEndPosition()),
                explode(':', $importedName)[1]
            );
        }

        $this->imported = [];
        $this->usedPrefixes = [];

        return [];
    }

    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'aliased import',
            source: <<<'PHP'
                <?php

                use Foobar as Barfoo;
                use Bagggg as Bazgar;

                new Barfoo();

                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Name "Bazgar" is imported but not used', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'aliased for used',
            source: <<<'PHP'
                <?php

                use Foobar as Barfoo;

                new Barfoo();

                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'imported in one namespace but used in another',
            source: <<<'PHP'
                <?php

                namespace One {
                    use Foo;
                }

                namespace Two {
                    new Foo();
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Name "Foo" is imported but not used', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'compact namespaced use',
            source: <<<'PHP'
                <?php

                namespace Foo\Foobar;

                use Foo\{Ham, Spam};

                new Ham();
                new Spam();
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'compact use unused',
            source: <<<'PHP'
                <?php

                use Foobar\{Barfoo};

                new Foobar();
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Name "Barfoo" is imported but not used', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'gh-1866',
            source: <<<'PHP'
                <?php

                namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

                use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
                use Phpactor\WorseReflection\Core\Name;
                use Phpactor\WorseReflection\Core\Type\StringLiteralType;

                class ReflectionDeclaredConstant
                {
                    private $name;
                    private ArgumentExpression $value;

                    public function name(): Name
                    {
                        if (!$this->name instanceof StringLiteralType) {
                        }
                        return Name::fromString($this->name);
                    }
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'namespaced unused imports',
            source: <<<'PHP'
                <?php

                namespace Foo;

                use Bar\Foobar;
                use Bag\Boo;

                new Boo();
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'namespaced used imports',
            source: <<<'PHP'
                <?php

                namespace Foo;

                use Bag\Boo;

                new Boo();
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'trait',
            source: <<<'PHP'
                <?php

                namespace Foo;

                use Bag\Boo;

                class Foobar {
                    use Boo;
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'unused imort',
            source: <<<'PHP'
                <?php

                use Foobar;
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
                Assert::assertEquals('Name "Foobar" is imported but not used', $diagnostics->at(0)->message());
            }
        );
        yield new DiagnosticExample(
            title: 'used by complex docblock',
            source: <<<'PHP'
                <?php

                namespace Bar;

                use Closure;

                /**
                 * @param string|Closure(string): string $bar
                 */
                function fo($bar): void
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'used by doctrine annotation',
            source: <<<'PHP'
                <?php

                use Doctrine\ORM;

                /**
                 * @ORM\Foo $bar
                 */
                function fo($foo): void
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'used by doctrine annotation aliased',
            source: <<<'PHP'
                <?php

                use Doctrine\ORM as Mapping;

                /**
                 * @Mapping\Foo $bar
                 */
                function fo($foo): void
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'used by dcotrine attribute',
            source: <<<'PHP'
                <?php

                use Doctrine\Mapping;

                #[Mapping\Id]
                function fo($foo): void
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'used by namespaced annotation',
            source: <<<'PHP'
                <?php

                use Foobar\Foo;

                /**
                 * @param Foo $bar
                 */
                function fo($foo): void
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'used by throws annotation',
            source: <<<'PHP'
                <?php

                use RuntimeException;

                /**
                 * @throws RuntimeException
                 */
                function fo($foo): void
                {
                }
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'used by import',
            source: <<<'PHP'
                <?php

                use Foobar;

                new Foobar();
                PHP,
            valid: true,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(0, $diagnostics);
            }
        );
        yield new DiagnosticExample(
            title: 'parent relative segment',
            source: <<<'PHP'
                <?php

                namespace Phpactor;

                use Dummy;

                class Test {
                    /**
                     * @var Dummy\Foo
                     */
                    private $foo;
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
        return 'unused_import';
    }

    private function extractDocblockNames(DocBlock $docblock, NodeContextResolver $resolver, Node $node): void
    {
        $prefix = sprintf('%s:', $this->getNamespaceName($node));

        $allTypes = [];
        foreach ($docblock->types() as $type) {
            if ($type instanceof AggregateType) {
                foreach ($type->allTypes() as $type) {
                    $allTypes[] = $type;
                }
            } else {
                $allTypes[] = $type;
            }
        }

        foreach ($allTypes as $type) {
            $this->usedPrefixes[$prefix . $type->__toString()] = true;
        }
    }

    /**
     * @param Node|Token $node
     */
    private function usedByAnnotation(string $contents, string $imported, $node): bool
    {
        $imported = explode(':', $imported)[1];
        return str_contains($contents, '@' . $imported);
    }

    /** @phpstan-ignore-next-line TP lies */
    private function lastPart(string $name): string
    {
        $parts = array_filter(explode('\\', $name));
        if (!$parts) {
            return '';
        }
        return $parts[array_key_last($parts)];
    }

    private function prefixedName(Node $node, string $name): string
    {
        return sprintf('%s:%s', $this->getNamespaceName($node), $name);
    }

    private function getNamespaceName(Node $node): string
    {
        $definition = $node->getNamespaceDefinition();
        if (null === $definition) {
            return '';
        }
        if (!$definition->name instanceof QualifiedName) {
            return '';
        }
        return (string)$definition->name;
    }
}
