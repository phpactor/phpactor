<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Model;

use Amp\Promise;
use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\NamespaceUseClause;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\LanguageServerProtocol\DocumentHighlight;
use Phpactor\LanguageServerProtocol\DocumentHighlightKind;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\EfficientLineCols;
use function Amp\call;
use function Amp\delay;

class Highlighter
{
    public function __construct(private Parser $parser)
    {
    }

    /**
     * @return Promise<Highlights>
     */
    public function highlightsFor(string $source, ByteOffset $offset): Promise
    {
        return call(function () use ($source, $offset) {
            dump('start');
            $offsets = [];
            $highlights = [];
            foreach ($this->generate($source, $offset) as $highlight) {
                yield delay(1);
                $offsets[] = $highlight->start;
                $offsets[] = $highlight->end;
                $highlights[] = $highlight;
            }
            dump('highlights');

            $lineCols = EfficientLineCols::fromByteOffsetInts($source, $offsets, true);
            $lspHighlights = [];

            foreach ($highlights as $highlight) {
                $startPos = $lineCols->get($highlight->start);
                $endPos = $lineCols->get($highlight->end);
                $lspHighlights[] = new DocumentHighlight(
                    new Range(
                        new Position($startPos->line() - 1, $startPos->col() - 1),
                        new Position($endPos->line() - 1, $endPos->col() - 1),
                    ),
                    $highlight->kind
                );
            }
            dump('done');
            return new Highlights(...$lspHighlights);
        });
    }

    /**
     * @return Generator<Highlight>
     */
    public function generate(string $source, ByteOffset $offset): Generator
    {
        $rootNode = $this->parser->parseSourceFile($source);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        if ($node instanceof Variable && $node->getFirstAncestor(PropertyDeclaration::class)) {
            yield from $this->properties($rootNode, (string)$node->getName());
            return;
        }

        if ($node instanceof Parameter) {
            yield from (null === $node->visibilityToken)
                ? $this->variables($rootNode, (string)$node->getName())
                : $this->properties($rootNode, (string)$node->getName())
            ;
            return;
        }

        if ($node instanceof Variable) {
            yield from $this->variables($rootNode, (string)$node->getName());
            return;
        }

        if ($node instanceof MethodDeclaration) {
            yield from $this->methods($rootNode, $node->getName());
            return;
        }

        if ($node instanceof ClassDeclaration) {
            yield from $this->namespacedNames($rootNode, (string)$node->getNamespacedName());
            return;
        }

        if ($node instanceof ConstElement) {
            yield from $this->constants($rootNode, (string)$node->getNamespacedName());
            return;
        }

        if ($node instanceof QualifiedName) {
            yield from $this->namespacedNames($rootNode, (string)$node->getResolvedName() ?: (string)$node->getNamespacedName());
            return;
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            $memberName = $node->memberName;
            if (!$memberName instanceof Token) {
                return;
            }
            yield from $this->memberAccess($rootNode, $node, (string)$memberName->getText($rootNode->getFileContents()));
            return;
        }

        if ($node instanceof MemberAccessExpression) {
            yield from $this->memberAccess($rootNode, $node, (string)$node->memberName->getText($rootNode->getFileContents()));
            return;
        }

        return;
    }

    /**
     * @return Generator<Highlight>
     */
    private function variables(SourceFileNode $rootNode, string $name): Generator
    {
        $name = $this->normalizeVarName($name);
        foreach ($rootNode->getDescendantNodes() as $childNode) {
            if ($childNode instanceof Variable && $childNode->getName() === $name) {
                yield new Highlight(
                    $childNode->getStartPosition(),
                    $childNode->getEndPosition(),
                    $this->variableKind($childNode)
                );
            }

            if ($childNode instanceof Parameter && $this->normalizeVarName((string)$childNode->variableName->getText($childNode->getFileContents())) === $name) {
                yield new Highlight(
                    $childNode->variableName->getStartPosition(),
                    $childNode->variableName->getEndPosition(),
                    DocumentHighlightKind::READ,
                );
            }
        }
    }

    /**
     * @return DocumentHighlightKind::*
     * @phpstan-ignore-next-line
     */
    private function variableKind(Node $node): int
    {
        $expression = $node->parent;
        if ($expression instanceof AssignmentExpression) {
            if ($expression->leftOperand === $node) {
                return DocumentHighlightKind::WRITE;
            }
        }

        return DocumentHighlightKind::READ;
    }

    /**
     * @return Generator<Highlight>
     */
    private function properties(Node $rootNode, string $name): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof Parameter && null !== $node->visibilityToken && (string)$node->getName() === $name) {
                yield new Highlight(
                    $node->variableName->getStartPosition(),
                    $node->variableName->getEndPosition(),
                    DocumentHighlightKind::TEXT,
                );
                continue;
            }

            if ($node instanceof Variable && $node->getFirstAncestor(PropertyDeclaration::class) && (string)$node->getName() === $name) {
                yield new Highlight(
                    $node->getStartPosition(),
                    $node->getEndPosition(),
                    DocumentHighlightKind::TEXT,
                );
            }

            if ($node instanceof MemberAccessExpression) {
                if ($name === $node->memberName->getText($rootNode->getFileContents())) {
                    yield new Highlight(
                        $node->memberName->getStartPosition(),
                        $node->memberName->getEndPosition(),
                        $this->variableKind($node),
                    );
                }
            }
        }
    }

    /**
     * @return Generator<Highlight>
     */
    private function memberAccess(SourceFileNode $rootNode, Node $node, string $memberName): Generator
    {
        if ($node->parent instanceof CallExpression) {
            return yield from $this->methods($rootNode, $memberName);
        }

        if (str_contains($node->getText(), '$')) {
            return yield from $this->properties($rootNode, $memberName);
        }

        return yield from $this->constants($rootNode, $memberName);
    }

    /**
     * @return Generator<Highlight>
     */
    private function methods(SourceFileNode $rootNode, string $name): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof MethodDeclaration && $node->getName() === $name) {
                yield new Highlight(
                    $node->name->getStartPosition(),
                    $node->name->getEndPosition(),
                    DocumentHighlightKind::TEXT,
                );
            }
            if ($node instanceof MemberAccessExpression) {
                if ($name === $node->memberName->getText($rootNode->getFileContents())) {
                    yield new Highlight(
                        $node->memberName->getStartPosition(),
                        $node->memberName->getEndPosition(),
                        $this->variableKind($node)
                    );
                }
            }
            if ($node instanceof ScopedPropertyAccessExpression) {
                $memberName = $node->memberName;
                if (!$memberName instanceof Token) {
                    return;
                }
                if ($name === $memberName->getText($rootNode->getFileContents())) {
                    yield new Highlight(
                        $memberName->getStartPosition(),
                        $memberName->getEndPosition(),
                        $this->variableKind($node)
                    );
                }
            }
        }
    }

    /**
     * @return Generator<Highlight>
     */
    private function constants(SourceFileNode $rootNode, string $name): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof ConstElement && (string)$node->getNamespacedName() === $name) {
                yield new Highlight(
                    $node->name->getStartPosition(),
                    $node->name->getEndPosition(),
                    DocumentHighlightKind::TEXT
                );
            }
            if ($node instanceof ScopedPropertyAccessExpression) {
                $memberName = $node->memberName;
                if (!$memberName instanceof Token) {
                    return;
                }
                if ($name === $memberName->getText($rootNode->getFileContents())) {
                    yield new Highlight(
                        $memberName->getStartPosition(),
                        $memberName->getEndPosition(),
                        $this->variableKind($node)
                    );
                }
            }
        }
    }

    /**
     * @return Generator<Highlight>
     */
    private function namespacedNames(Node $rootNode, string $fullyQualfiedName): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof NamespaceUseClause && (string) $node->namespaceName === $fullyQualfiedName) {
                $nameParts = $node->namespaceName->nameParts;
                $name = end($nameParts);

                yield new Highlight(
                    $name->getStartPosition(),
                    $name->getEndPosition(),
                    DocumentHighlightKind::TEXT
                );
            }
            if ($node instanceof ClassDeclaration && (string)$node->getNamespacedName() === $fullyQualfiedName) {
                yield new Highlight(
                    $node->name->getStartPosition(),
                    $node->name->getEndPosition(),
                    DocumentHighlightKind::TEXT
                );
            }
            if ($node instanceof QualifiedName) {
                if ($fullyQualfiedName === (string)$node->getResolvedName()) {
                    yield new Highlight(
                        $node->getStartPosition(),
                        $node->getEndPosition(),
                        $this->variableKind($node)
                    );
                }
            }
        }
    }

    private function normalizeVarName(string $varName): string
    {
        return ltrim($varName, '$');
    }
}
