<?php

namespace Phpactor\Search\Adapter\TolerantParser\NodeMatcher;

use Microsoft\PhpParser\Node;
use Phpactor\Search\Adapter\TolerantParser\NodeMatcher;

class NodeEqualityMatcher implements NodeMatcher
{
    /**
     * @param array<string,MatchToken> $matchTokens
     */
    private function isMatching(Node $node, Node $template, array &$matchTokens): bool
    {
        if (false === $this->areNodesComparable($node, $template)) {
            return false;
        }


        $matched = true;
        foreach ($template::CHILD_NAMES as $childName) {
            $nodePropNodes = $this->normalize($node->$childName);
            $templatePropNodes = $this->normalize($template->$childName);

            $matchOneOf = $this->shouldMatchOneOf($node, $childName);

            foreach ($templatePropNodes as $index => $templatePropNode) {
                if (false === $matchOneOf) {
                    $nodeProp = $nodePropNodes[$index] ?? null;
                    $isMatch = $this->nodesMatch($template, $templatePropNode, $node, $nodeProp, $matchTokens);

                    if (false === $isMatch) {
                        return false;
                    }
                    continue;
                }

                if (empty($nodePropNodes)) {
                    return false;
                }

                // if applicable, iterate over list (e.g. list of method declarations) and match at least one
                foreach ($nodePropNodes as $nodeProp) {
                    $isMatch = $this->nodesMatch($template, $templatePropNode, $node, $nodeProp, $matchTokens);

                    if ($isMatch) {
                        $matched = true;
                        break;
                    }

                    $matched = false;
                }
            }
        }

        return $matched;
    }

    private function areNodesComparable(Node $node1, Node $node2): bool
    {
        return get_class($node1) === get_class($node2);
    }
}
