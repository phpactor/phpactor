<?php

namespace Phpactor\TolerantAstDiff;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use RuntimeException;

final class AstDiff
{
    private SourceFileNode $fileSource1;

    private SourceFileNode $rightSource;

    public function merge(Node $node1, Node $node2): void
    {
        $this->fileSource1 = $node1->getRoot();
        $this->rightSource = $node2->getRoot();

        $this->doMerge($node1, $node2);
    }

    private function doMerge(Node $leftNode, Node $rightNode): void
    {
        if ($leftNode->getFullText() === $rightNode->getFullText()) {
            return;
        }

        if ($leftNode::class !== $rightNode::class) {
            throw new RuntimeException(sprintf(
                'Can only compare nodes of the same type, got: %s vs %s',
                $leftNode::class,
                $rightNode::class
            ));
        }

        $node1ChildNames = $leftNode->getChildNames();
        $node2ChildNames = $rightNode->getChildNames();

        $lastPosition = $leftNode->getFullStartPosition();
        foreach ($node1ChildNames as $childName) {

            $leftMember = $leftNode->$childName;
            $rightMember = $rightNode->$childName;

            self::assertNullOrNodeToken($leftMember);
            self::assertNullOrNodeToken($rightMember);

            // this property is a list of nodes we need to figure out which
            // items to add, remove or update
            if (is_array($leftMember) && is_array($rightMember)) {
                $elementIndex = -1;
                foreach (array_keys($leftMember) as $elementIndex) {
                    $leftElement = $leftMember[$elementIndex];
                    $rightElement = $rightMember[$elementIndex] ?? null;

                    $lastPosition = $leftElement->getFullStartPosition();

                    if ($rightElement === null) {

                        // if there's no corresponding node in the second AST
                        // then remove it's correspondant and all subsequent
                        // nodes from the list
                        $this->removeChildFrom($leftNode, $childName, $elementIndex);

                        // we can break early noow
                        break;
                    }

                    if ($leftElement instanceof Token) {
                        // if we have a single token, then just replace it
                        // with the corresponding token/node/null

                        /** @phpstan-ignore-next-line */
                        $leftNode->$childName[$elementIndex] = $rightElement;
                        $replacement = $rightElement->getFullText(
                            $this->rightSource->getFileContents()
                        );
                        $this->applyEdit(TextEdit::create(
                            $leftElement->getFullStartPosition(),
                            $leftElement->getFullWidth(),
                            $replacement,
                        ));

                        continue;
                    }

                    // if the class type is different then it's
                    // replace the whole subtree.
                    if ($leftElement::class !== $rightElement::class) {
                        /** @phpstan-ignore-next-line */
                        $leftNode->$childName[$elementIndex] = $rightElement;
                        $this->applyEdit(TextEdit::create(
                            $leftElement->getFullStartPosition(),
                            $leftElement->getFullWidth(),
                            $rightElement->getFullText($this->rightSource->getFileContents()),
                        ));
                        continue;
                    }

                    if ($rightElement instanceof Node) {
                        // recurse on the listed node:
                        $this->doMerge($leftElement, $rightElement);
                        continue;
                    }
                }

                // if we get here then we are adding elements
                // so we take all elements after the last index
                // (or after -1 if we didn't enter the above loop).
                $this->appendChildren($leftNode, $childName, array_slice($rightMember, ++$elementIndex));

                // we're done with this list property, move on to
                // the next property in the Node.
                continue;
            }

            if (is_array($leftMember) || is_array($rightMember)) {
                throw new \RuntimeException('Should not happen');
            }

            // it's possible that property is NULL, if it's not then the last
            // position of the last property would be used by ommission
            if ($leftMember instanceof Node || $leftMember instanceof Token) {
                $lastPosition = $leftMember->getStartPosition();
            }

            // if the right member is NULL then "unset" it nn the left
            if ($rightMember === null && ($leftMember instanceof Node || $leftMember instanceof Token)) {
                $leftNode->$childName = null;
                $this->applyEdit(TextEdit::create(
                    $leftMember->getFullStartPosition(),
                    $leftMember->getFullWidth(),
                    '',
                ));
                continue;
            }

            // if right member is a node and left is NULL or a node or token of a different type then replace it
            if ($rightMember instanceof Node && ($leftMember === null || $leftMember::class !== $rightMember::class)) {
                // update the reference content in the source node by
                // applying a text edit - we'll reindex the offsets later.
                $leftNode->$childName = $this->copyNode($rightMember);
                $this->applyEdit(TextEdit::create(
                    $lastPosition,
                    $leftMember?->getFullWidth() ?? 0,
                    $rightMember->getFullText(),
                ));
                continue;
            }

            // if we got here then the nodes are of the same class so let's
            // merge them
            if ($rightMember instanceof Node && $leftMember instanceof Node) {
                $this->doMerge($leftMember, $rightMember);
                continue;
            }

            // if the right member is a token then just replace it
            if ($rightMember instanceof Token) {
                $replacement = $rightMember->getFullText($this->rightSource->getFileContents());
                $leftNode->$childName = $this->copyNode($rightMember);
                $this->applyEdit(TextEdit::create(
                    $leftMember?->getFullStartPosition() ?? $lastPosition,
                    $leftMember?->getFullWidth() ?? 0,
                    $replacement,
                ));
                continue;
            }

            // nothing and nothing is nothing
            if ($rightMember === null && $leftMember === null) {
                continue;
            }

            throw new \RuntimeException(sprintf(
                'Do not know what to do with %s vs. %s',
                get_debug_type($leftMember), get_debug_type($rightMember)
            ));
        }

        return;
    }

    private function removeChildFrom(Node $parent, string $childName, int $index): void
    {
        /** @var list<Node> */
        $existingNodes = $parent->$childName;
        // we need to figure out the length of the nodes we're removing
        // so that we can compensate
        $removedNodes = array_slice($existingNodes, $index);

        // if we didn't remove anything then there's nothing to see here.
        if (count($removedNodes) === 0) {
            return;
        }

        // we want to truncate the source code from this point
        $firstRemovedNode = $removedNodes[0];

        // update the AST with just the nodes that were not removed
        $keepNodes = array_slice($existingNodes, 0, $index);
        $parent->$childName = $keepNodes;

        $removeLength = array_sum(array_map(
            fn (Node|Token $node) => $node->getFullWidth() ?? 0,
            $removedNodes
        ));

        $this->applyEdit(
            TextEdit::create(
                $firstRemovedNode->getFullStartPosition(),
                $removeLength,
                ''
            )
        );
    }

    /**
     * @param list<Node|Token> $newNodes
     */
    private function appendChildren(Node $parent, string $childName, array $newNodes): void
    {
        $newNodes = array_map(function (Node|Token $node) {
            return $this->copyNode($node);
        }, $newNodes);
        if (empty($newNodes)) {
            return;
        }
        $firstNewNode = $newNodes[array_key_first($newNodes)];

        /** @var Node[] */
        $existingNodes = $parent->$childName;
        $lastExistingNode = $newNodes[array_key_last($newNodes)];
        $parent->$childName = array_merge($existingNodes, $newNodes);

        $addLength = array_sum(array_map(
            fn (Node|Token $node) => $node->getFullWidth(),
            $newNodes
        ));

        $addContent = substr(
            $this->rightSource->getFileContents(),
            $firstNewNode->getFullStartPosition(),
            $addLength,
        );

        $this->applyEdit(
            TextEdit::create(
                $lastExistingNode->getFullStartPosition(),
                0,
                $addContent,
            ),
        );

    }

    private function applyEdit(TextEdit $edit): void

    {
        $source = $this->fileSource1;
        $source->fileContents = TextEdits::one($edit)->apply($source->getFileContents());
        self::reindex($this->fileSource1);
    }

    private static function reindex(Node $node): void
    {
        $offset = 0;
        foreach ($node->getDescendantTokens() as $token) {
            $leading = $token->start - $token->fullStart;

            $token->fullStart = $offset;
            $token->start = $offset + $leading;

            $offset += $token->length;
        }
    }

    private function copyNode(Node|Token $node): Node|Token
    {
        return $node;
    }

    /**
     * @phpstan-assert null|Token|Node|list<Token|Node> $node
     */
    private static function assertNullOrNodeToken(mixed $node): void
    {
        if ($node === null || $node instanceof Node || $node instanceof Token || is_array($node)) {
            return;
        }

        throw new \RuntimeException(sprintf('Invalid node property type: %s', get_debug_type($node)));
    }
}
