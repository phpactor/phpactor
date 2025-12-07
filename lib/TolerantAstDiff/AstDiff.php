<?php

namespace Phpactor\TolerantAstDiff;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Token;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use RuntimeException;
use function DeepCopy\deep_copy;

final class AstDiff
{
    private SourceFileNode $fileSource1;

    private SourceFileNode $fileSource2;

    private array $visited = [];

    public function merge(Node $node1, Node $node2): void
    {
        $this->visited = [];
        $this->fileSource1 = $node1->getRoot();
        $this->fileSource2 = $node2->getRoot();

        $this->doMerge($node1, $node2);
    }

    private function doMerge(Node $node1, Node $node2): void
    {
        if ($node1->getFullText() === $node2->getFullText()) {
            return;
        }

        // check for circular references
        // TODO: this was added for debugging can probably be removed
        if (isset($this->visited[spl_object_id($node1)])) {
            throw new \RuntimeException('Circular reference');
        }

        $this->visited[spl_object_id($node1)] = true;

        if ($node1::class !== $node2::class) {
            throw new RuntimeException(sprintf(
                'Can only compare nodes of the same type, got: %s vs %s',
                $node1::class,
                $node2::class
            ));
        }

        $node1ChildNames = $node1->getChildNames();
        $node2ChildNames = $node2->getChildNames();

        $lastPosition = $node1->getFullStartPosition();
        foreach ($node1ChildNames as $childName) {

            $member1 = $node1->$childName;
            $member2 = $node2->$childName;

            // this property is a list of nodes we need to figure out which
            // items to add, remove or update
            if (is_array($member1)) {

                /** @var list<Token|Node> $member1 */
                /** @var list<Token|Node> $member2 */

                $elementIndex = -1;
                foreach (array_keys($member1) as $elementIndex) {
                    $node1Child = $member1[$elementIndex];
                    $node2Child = $member2[$elementIndex] ?? null;

                    $lastPosition = $node1Child->getFullStartPosition();

                    if ($node2Child === null) {

                        // if there's no corresponding node in the second AST
                        // then remove it's correspondant and all subsequent
                        // nodes from the list
                        $this->removeChildFrom($node1, $childName, $elementIndex);

                        // we can break early noow
                        break;
                    }

                    if ($node1Child instanceof Token) {
                        // if we have a single token, then just replace it
                        // with the corresponding token/node/null

                        /** @phpstan-ignore-next-line */
                        $node1->$childName[$elementIndex] = $node2Child;
                        $replacement = $node2Child->getFullText(
                            $this->fileSource2->getFileContents()
                        );
                        $this->applyEdit(TextEdit::create(
                            $node1Child->getFullStartPosition(),
                            $node1Child->getFullWidth(),
                            $replacement,
                        ));

                        continue;
                    }

                    // if the class type is different then it's
                    // replace the whole subtree.
                    if ($node1Child::class !== $node2Child::class) {
                        /** @phpstan-ignore-next-line */
                        $node1->$childName[$elementIndex] = $node2Child;
                        $this->applyEdit(TextEdit::create(
                            $node1Child->getFullStartPosition(),
                            $node1Child->getFullWidth(),
                            $node2Child->getFullText(),
                        ));
                        continue;
                    }

                    if ($node2Child instanceof Node) {
                        // recurse on the listed node:
                        $this->doMerge($node1Child, $node2Child);
                        continue;
                    }
                }

                // if we get here then we are adding elements
                // so we take all elements after the last index
                // (or after -1 if we didn't enter the above loop).
                $this->appendChildren($node1, $childName, array_slice($member2, ++$elementIndex));

                // we're done with this list property, move on to
                // the next property in the Node.
                continue;
            }

            // it's possible that property is NULL, if it's not then the last
            // position of the last property would be used by ommission
            if ($member1 instanceof Node || $member1 instanceof Token) {
                $lastPosition = $member1->getStartPosition();
            }

            // if the original member is NULL but the new member is
            // NOT null then just replace it
            if ($member2 === null && $member1 !== null) {
                $node1->$childName = $this->copyNode($member2);
                $this->applyEdit(TextEdit::create(
                    $lastPosition,
                    0,
                    $member2?->getFullText($this->fileSource2->getFileContents()) ?? '',
                ));
                continue;
            }

            // if the members are both nodes then just replace it and continue
            if ($member2 instanceof Node && $member2::class !== $member1::class) {
                // update the reference content in the source node by
                // applying a text edit - we'll reindex the offsets later.
                $node1->$childName = $this->copyNode($member2);
                $this->applyEdit(TextEdit::create(
                    $lastPosition,
                    $member1?->getFullWidth(),
                    $member2?->getFullText($this->fileSource2->getFileContents()) ?? '',
                ));
                continue;
            }

            // if the new member is a token then just replace it
            if ($member2 instanceof Token) {
                /** @phpstan-ignore-next-line */
                $replacement = $member2->getFullText($this->fileSource2->getFileContents());
                $node1->$childName = $this->copyNode($member2);
                $this->applyEdit(TextEdit::create(
                    $member1->getFullStartPosition(),
                    $member1->getFullWidth(),
                    $replacement ?? '',
                ));
                continue;
            }

            // if we got here then the nodes are of the same class so let's
            // merge them
            if ($member2 instanceof Node && $member1 instanceof Node) {
                $this->doMerge($member1, $member2);
                continue;
            }

            // nothing and nothing is nothing
            if ($member2 === null && $member1 === null) {
                continue;
            }

            throw new \RuntimeException(sprintf(
                'Do not know what to do with %s vs. %s',
                get_debug_type($member1), get_debug_type($member2)
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
            fn (Node|Token $node) => $node->getFullWidth(),
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
            $this->fileSource2->getFileContents(),
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
}
