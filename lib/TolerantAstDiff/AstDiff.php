<?php

namespace Phpactor\TolerantAstDiff;

use Microsoft\PhpParser\Node;
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

    public function merge(Node $node1, Node $node2): void
    {
        $this->fileSource1 = $node1->getRoot();
        $this->fileSource2 = $node2->getRoot();

        $this->doMerge($node1, $node2);
    }

    private function doMerge(Node $node1, Node $node2): void
    {
        if ($node1::class !== $node2::class) {
            throw new RuntimeException(sprintf(
                'Can only compare nodes of the same type, got: %s vs %s',
                $node1::class,
                $node2::class
            ));
        }


        if ($this->isSame($node1, $node2)) {
            return;
        }

        $this->mapNode($node1, $node2);

        $node1ChildNames = $node1->getChildNames();
        $node2ChildNames = $node2->getChildNames();

        foreach ($node1ChildNames as $childName) {

            $node1Prop = $node1->$childName;
            $node2Prop = $node2->$childName;

            if (is_array($node1Prop)) {
                assert(is_array($node2Prop));
                $offset = -1;
                foreach (array_keys($node1Prop) as $offset) {
                    $node1Child = $node1Prop[$offset];
                    $node2Child = $node2Prop[$offset] ?? null;

                    if ($node2Child === null) {
                        $this->removeChildFrom($node1, $childName, $offset);
                        break;
                    }

                    if ($node1Child instanceof Token) {
                        continue;
                    }

                    if (!$node1Child instanceof Node || !$node2Child instanceof Node) {
                        // should never happen
                        continue;
                    }

                    if ($node1Child::class !== $node2Child::class) {
                        $node1->$childName[$offset] = $node2Child;
                        $this->applyEdit($node1, TextEdit::create(
                            $node1Child->getFullStartPosition(),
                            $node1Child->getFullWidth(),
                            $node2Child->getFullText(),
                        ));
                        continue;
                    }

                    $this->doMerge($node1Child, $node2Child);
                }

                $this->appendChildren($node1, $childName, array_slice($node2Prop, ++$offset));
                continue;
            }

            if ($node1Prop instanceof Node) {
                $this->doMerge($node1Prop, $node2Prop);
            }
        }

        return;
    }

    /**
     * Compare the inner node content
     */
    private static function isSame(Node $node1, Node $node2): bool
    {
        return $node1->getFullText() === $node2->getFullText();
    }

    private function removeChildFrom(Node $parent, string $childName, int $offset): void
    {
        /** @var list<Node> */
        $keepNodes = $parent->$childName;
        $removedNodes = array_slice($keepNodes, $offset);

        if (count($removedNodes) === 0) {
            return;
        }

        $firstRemovedNode = $removedNodes[0];
        $keepNodes = array_slice($keepNodes, 0, $offset);

        $parent->$childName = $keepNodes;

        $removeLength = array_sum(array_map(fn (Node|Token $node) => $node->getFullWidth(), $removedNodes));
        $this->applyEdit(
            $parent,
            TextEdit::create($firstRemovedNode->getFullStartPosition(), $removeLength, '')
        );
    }

    /**
     * @param list<Node> $newNodes
     */
    private function appendChildren(Node $parent, string $childName, array $newNodes): void
    {
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
            $parent,
            TextEdit::create(
                $lastExistingNode->getFullStartPosition(),
                0,
                $addContent,
            ),
        );

    }

    private function applyEdit(Node $node, TextEdit $edit): void
    {
        $source = $this->fileSource1;
        $distance = strlen($edit->replacement()) - $edit->length();
        $existing = substr($source, $edit->start()->toInt(), $edit->length());

        if ($existing === $edit->replacement()) {
            return;
        }

        foreach ($source->getDescendantTokens() as $token) {
            if ($token->getFullStartPosition() < $edit->start()->toInt()) {
                continue;
            }
//                dump(
//                    Token::getTokenKindNameFromValue($token->kind),
//                    $token->getText($source)
//                );
            $token->start += $distance;
            $token->fullStart += $distance;
        }

        $source->fileContents = TextEdits::one($edit)->apply($source->getFileContents());
    }

    private function mapNode(Node $node1, Node $node2): void
    {
        if ($node2::class !== $node1::class) {
            throw new RuntimeException(sprintf(
                'Can only map nodes of the same type to eachother, got %s and %s',
                $node2::class,
                $node1::class
            ));
        }

        $lastPosition = $node1->getFullStartPosition();
        foreach ($node1->getChildNames() as $childName) {
            $member1 = $node1->$childName;
            $member2 = $node2->$childName;

            if (is_array($member1)) {
                foreach ($member1 as $member) {
                    assert($member instanceof Token || $member instanceof Node);
                    $lastPosition = $member->getFullStartPosition();
                }
                continue;
            }

            if ($member1 instanceof Node || $member1 instanceof Token) {
                $lastPosition = $member1->getFullStartPosition();
            }

            if ($member2 instanceof Token || $member2 === null) {
                $this->applyEdit($node1, TextEdit::create(
                    $lastPosition,
                    $member1?->getFullWidth() ?? 0,
                    $member2?->getFullText($this->fileSource2->getFileContents()) ?? '',
                ));
                if ($member2 !== null) {
                    // TODO: do we care if we modify the "new" AST by reference?
                    $member2 = deep_copy($member2);
                }
                $node1->$childName = $member2;
                continue;
            }
        }
    }
}
