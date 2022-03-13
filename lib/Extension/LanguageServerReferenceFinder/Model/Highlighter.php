<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Model;

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
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\LanguageServerProtocol\DocumentHighlight;
use Phpactor\LanguageServerProtocol\DocumentHighlightKind;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\TextDocument\ByteOffset;

class Highlighter
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    public function highlightsFor(string $source, ByteOffset $offset): Highlights
    {
        $rootNode = $this->parser->parseSourceFile($source);
        $node = $rootNode->getDescendantNodeAtPosition($offset->toInt());

        if ($node instanceof Variable && $node->getFirstAncestor(PropertyDeclaration::class)) {
            return Highlights::fromIterator($this->properties($rootNode, $node->getName()), );
        }

        if ($node instanceof Parameter) {
            return Highlights::fromIterator($this->variables($rootNode, (string)$node->getName()));
        }

        if ($node instanceof Variable) {
            return Highlights::fromIterator($this->variables($rootNode, (string)$node->getName()));
        }

        if ($node instanceof MethodDeclaration) {
            return Highlights::fromIterator($this->methods($rootNode, $node->getName()));
        }

        if ($node instanceof ClassDeclaration) {
            return Highlights::fromIterator($this->namespacedNames($rootNode, (string)$node->getNamespacedName()));
        }

        if ($node instanceof ConstElement) {
            return Highlights::fromIterator($this->constants($rootNode, (string)$node->getNamespacedName()));
        }

        if ($node instanceof QualifiedName) {
            return Highlights::fromIterator($this->namespacedNames($rootNode, (string)$node->getResolvedName() ?: (string)$node->getNamespacedName()));
        }

        if ($node instanceof ScopedPropertyAccessExpression) {
            $memberName = $node->memberName;
            if (!$memberName instanceof Token) {
                return Highlights::empty();
            }
            return Highlights::fromIterator($this->memberAccess($rootNode, $node, (string)$memberName->getText($rootNode->getFileContents())));
        }

        if ($node instanceof MemberAccessExpression) {
            return Highlights::fromIterator($this->memberAccess($rootNode, $node, (string)$node->memberName->getText($rootNode->getFileContents())));
        }

        return Highlights::empty();
    }

    /**
     * @return Generator<DocumentHighlight>
     */
    private function variables(SourceFileNode $rootNode, string $name): Generator
    {
        $name = $this->normalizeVarName($name);
        foreach ($rootNode->getDescendantNodes() as $childNode) {
            if ($childNode instanceof Variable && $childNode->getName() === $name) {
                yield new DocumentHighlight(
                    new Range(
                        PositionConverter::intByteOffsetToPosition($childNode->getStartPosition(), $childNode->getFileContents()),
                        PositionConverter::intByteOffsetToPosition($childNode->getEndPosition(), $childNode->getFileContents())
                    ),
                    $this->variableKind($childNode)
                );
            }

            if ($childNode instanceof Parameter && $this->normalizeVarName((string)$childNode->variableName->getText($childNode->getFileContents())) === $name) {
                yield new DocumentHighlight(
                    new Range(
                        PositionConverter::intByteOffsetToPosition($childNode->variableName->getStartPosition(), $childNode->getFileContents()),
                        PositionConverter::intByteOffsetToPosition($childNode->variableName->getEndPosition(), $childNode->getFileContents()),
                    ),
                    DocumentHighlightKind::READ
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
     * @return Generator<DocumentHighlight>
     */
    private function properties(Node $rootNode, string $name): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof Variable && $node->getFirstAncestor(PropertyDeclaration::class) && (string)$node->getName() === $name) {
                yield new DocumentHighlight(
                    new Range(
                        PositionConverter::intByteOffsetToPosition($node->getStartPosition(), $node->getFileContents()),
                        PositionConverter::intByteOffsetToPosition($node->getEndPosition(), $node->getFileContents())
                    ),
                    DocumentHighlightKind::TEXT
                );
            }

            if ($node instanceof MemberAccessExpression) {
                if ($name === $node->memberName->getText($rootNode->getFileContents())) {
                    yield new DocumentHighlight(
                        new Range(
                            PositionConverter::intByteOffsetToPosition($node->memberName->getStartPosition(), $node->getFileContents()),
                            PositionConverter::intByteOffsetToPosition($node->memberName->getEndPosition(), $node->getFileContents())
                        ),
                        $this->variableKind($node)
                    );
                }
            }
        }
    }

    /**
     * @return Generator<DocumentHighlight>
     */
    private function memberAccess(SourceFileNode $rootNode, Node $node, string $memberName): Generator
    {
        if ($node->parent instanceof CallExpression) {
            return yield from $this->methods($rootNode, $memberName);
        }

        if (false !== strpos($node->getText(), '$')) {
            return yield from $this->properties($rootNode, $memberName);
        }

        return yield from $this->constants($rootNode, $memberName);
    }

    /**
     * @return Generator<DocumentHighlight>
     */
    private function methods(SourceFileNode $rootNode, string $name): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof MethodDeclaration && $node->getName() === $name) {
                yield new DocumentHighlight(
                    new Range(
                        PositionConverter::intByteOffsetToPosition($node->name->getStartPosition(), $node->getFileContents()),
                        PositionConverter::intByteOffsetToPosition($node->name->getEndPosition(), $node->getFileContents())
                    ),
                    DocumentHighlightKind::TEXT
                );
            }
            if ($node instanceof MemberAccessExpression) {
                if ($name === $node->memberName->getText($rootNode->getFileContents())) {
                    yield new DocumentHighlight(
                        new Range(
                            PositionConverter::intByteOffsetToPosition($node->memberName->getStartPosition(), $node->getFileContents()),
                            PositionConverter::intByteOffsetToPosition($node->memberName->getEndPosition(), $node->getFileContents())
                        ),
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
                    yield new DocumentHighlight(
                        new Range(
                            PositionConverter::intByteOffsetToPosition($memberName->getStartPosition(), $node->getFileContents()),
                            PositionConverter::intByteOffsetToPosition($memberName->getEndPosition(), $node->getFileContents())
                        ),
                        $this->variableKind($node)
                    );
                }
            }
        }
    }

    /**
     * @return Generator<DocumentHighlight>
     */
    private function constants(SourceFileNode $rootNode, string $name): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof ConstElement && (string)$node->getNamespacedName() === $name) {
                yield new DocumentHighlight(
                    new Range(
                        PositionConverter::intByteOffsetToPosition($node->name->getStartPosition(), $node->getFileContents()),
                        PositionConverter::intByteOffsetToPosition($node->name->getEndPosition(), $node->getFileContents())
                    ),
                    DocumentHighlightKind::TEXT
                );
            }
            if ($node instanceof ScopedPropertyAccessExpression) {
                $memberName = $node->memberName;
                if (!$memberName instanceof Token) {
                    return;
                }
                if ($name === $memberName->getText($rootNode->getFileContents())) {
                    yield new DocumentHighlight(
                        new Range(
                            PositionConverter::intByteOffsetToPosition($memberName->getStartPosition(), $node->getFileContents()),
                            PositionConverter::intByteOffsetToPosition($memberName->getEndPosition(), $node->getFileContents())
                        ),
                        $this->variableKind($node)
                    );
                }
            }
        }
    }

    /**
     * @return Generator<DocumentHighlight>
     */
    private function namespacedNames(Node $rootNode, string $fullyQualfiiedName): Generator
    {
        foreach ($rootNode->getDescendantNodes() as $node) {
            if ($node instanceof ClassDeclaration && (string)$node->getNamespacedName() === $fullyQualfiiedName) {
                yield new DocumentHighlight(
                    new Range(
                        PositionConverter::intByteOffsetToPosition($node->name->getStartPosition(), $node->getFileContents()),
                        PositionConverter::intByteOffsetToPosition($node->name->getEndPosition(), $node->getFileContents())
                    ),
                    DocumentHighlightKind::TEXT
                );
            }
            if ($node instanceof QualifiedName) {
                if ($fullyQualfiiedName === (string)$node->getResolvedName()) {
                    yield new DocumentHighlight(
                        new Range(
                            PositionConverter::intByteOffsetToPosition($node->getStartPosition(), $node->getFileContents()),
                            PositionConverter::intByteOffsetToPosition($node->getEndPosition(), $node->getFileContents())
                        ),
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
