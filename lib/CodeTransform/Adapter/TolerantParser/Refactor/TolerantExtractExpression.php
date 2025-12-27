<?php

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Microsoft\PhpParser\FunctionLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\StatementNode;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use function end;
use function iterator_to_array;
use function preg_match;

class TolerantExtractExpression implements ExtractExpression
{
    public function __construct(private readonly AstProvider $parser = new TolerantAstProvider())
    {
    }

    public function canExtractExpression(SourceCode $source, int $offsetStart, ?int $offsetEnd = null): bool
    {
        return $this->getExtractedExpression($source, $offsetStart, $offsetEnd) !== null;
    }

    public function extractExpression(SourceCode $source, int $offsetStart, ?int $offsetEnd, string $variableName): TextEdits
    {
        $expression = $this->getExtractedExpression($source, $offsetStart, $offsetEnd);
        if ($expression === null) {
            return TextEdits::none();
        }

        $startPosition = $expression->getStartPosition();
        $endPosition = $expression->getEndPosition();

        $extractedString = rtrim(trim($source->extractSelection($startPosition, $endPosition)), ';');
        $assigment = sprintf('$%s = %s;', $variableName, $extractedString) . "\n";

        $statement = $expression->getFirstAncestor(StatementNode::class);
        assert($statement instanceof StatementNode);

        $edits = $this->resolveEdits($statement, $expression, $extractedString, $assigment, $variableName);

        return TextEdits::fromTextEdits($edits);
    }

    private function getExtractedExpression(SourceCode $source, int $offsetStart, ?int $offsetEnd): ?Expression
    {
        // only apply to selections
        if ($offsetStart === $offsetEnd) {
            return null;
        }
        $rootNode = $this->parser->get($source);
        $startNode = $rootNode->getDescendantNodeAtPosition($offsetStart);

        if ($offsetEnd) {
            $endNode = $rootNode->getDescendantNodeAtPosition($offsetEnd);
            $expression = $this->getCommonExpression($startNode, $endNode);

            if ($expression === null && $endNode instanceof ExpressionStatement) {
                // <expression-statement> := <expression>;
                // check if $endNode does not contain the semi-colon
                // then find the last child expression that ends at the semi-colon
                assert($endNode instanceof ExpressionStatement);
                $expressions = array_filter(
                    iterator_to_array($endNode->getDescendantNodes(), false),
                    function (Node $item) use ($endNode) {
                        return
                            $item instanceof Expression &&
                            $item->getEndPosition() == $endNode->expression->getEndPosition();
                    }
                );

                if ($expressions === []) {
                    return null;
                }

                $expression = $this->getCommonExpression($startNode, end($expressions));
            }
        } else {
            $expression = $this->outerExpression($startNode);
        }

        if ($expression === null) {
            return null;
        }

        return $expression;
    }

    private function getCommonExpression(Node $node1, Node $node2): ?Expression
    {
        if ($node1 === $node2 && $node1 instanceof Expression) {
            return $node1;
        }
        $ancestor = $node1;
        $expressions = [];
        if ($node1 instanceof Expression) {
            $expressions[] = $node1;
        }

        while (($ancestor = $ancestor->parent) !== null) {
            if ($ancestor instanceof FunctionLike) {
                break;
            }
            if ($ancestor instanceof Expression === false) {
                continue;
            }
            $expressions[] = $ancestor;
        }

        if (empty($expressions)) {
            return null;
        }

        $ancestor = $node2;
        if (in_array($ancestor, $expressions, true)) {
            return $ancestor;
        }
        while (($ancestor = $ancestor->parent) !== null) {
            if (in_array($ancestor, $expressions, true)) {
                return $ancestor;
            }
        }

        return null;
    }

    /**
     * @return array<TextEdit>
     */
    private function resolveEdits(
        Node $statement,
        Node $expression,
        string $extractedString,
        string $assignment,
        string $variableName
    ): array {
        if ($statement instanceof ExpressionStatement && $statement->expression === $expression) {
            return [
                TextEdit::create($statement->getStartPosition(), $statement->getWidth(), $assignment)
            ];
        }

        $matches = [];
        $indentation = '';
        if (preg_match('/(\t| )*$/', $statement->getLeadingCommentAndWhitespaceText(), $matches) > 0) {
            $indentation = $matches[0];
        }

        return [
            TextEdit::create($statement->getStartPosition(), 0, $assignment . $indentation),
            TextEdit::create($expression->getStartPosition(), strlen($extractedString), '$' . $variableName),
        ];
    }

    private function outerExpression(Node $node, ?Node $originalNode = null): ?Expression
    {
        $originalNode = $originalNode ?: $node;

        $parent = $node->getParent();

        if (null === $parent) {
            return $node instanceof Expression ? $node : null;
        }

        if ($parent->getStartPosition() !== $originalNode->getStartPosition() && $originalNode instanceof Expression) {
            return $originalNode;
        }

        return $this->outerExpression($parent, $originalNode);
    }
}
