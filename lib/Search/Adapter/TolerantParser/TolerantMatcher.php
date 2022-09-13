<?php

namespace Phpactor\Search\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Search\Model\Matcher;
use Phpactor\Search\Model\Matches;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;

class TolerantMatcher implements Matcher
{
    private Parser $parser;

    public function __construct(Parser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Find all nodes matching first node of pattern
     * Within those nodes find immediate children matching secod node of pattern
     */
    public function match(TextDocument $document, string $pattern): Matches
    {
        $patternNode = $this->parser->parseSourceFile('<?php ' . $pattern);
        $documentNode = $this->parser->parseSourceFile($document);
        $matches = [];
        $patternNode = $this->reducePattern($patternNode);
        $baseNodes = $this->traverseDocument($documentNode, $patternNode);
        foreach ($baseNodes as $baseNode) {
            if ($this->nodeMatches($baseNode, $patternNode)) {
                $matches[] = new PatternMatch(ByteOffsetRange::fromInts($baseNode->getStartPosition(), $baseNode->getEndPosition()));
            }
        }

        return new Matches($matches);
    }

    /**
     * @return array<Node>
     * @param array<Node> $matches
     */
    private function traverseDocument(Node $documentNode, Node $targetNode, array &$matches = []): array
    {
        foreach ($documentNode->getChildNodes() as $childNode) {
            if (get_class($childNode) === get_class($targetNode)) {
                $matches[] = $childNode;
            }

            $this->traverseDocument($childNode, $targetNode, $matches);
        }

        return $matches;
    }

    /**
     * @return bool
     */
    private function nodeMatches(Node $node, Node $toMatch): bool
    {
        foreach ($toMatch->getChildNodesAndTokens() as $name => $matchNodeOrToken) {
            if ($matchNodeOrToken !== null) {

                // candidate does not have required token or node
                if (!isset($node->$name) || null === $node->$name) {
                    return false;
                }

                foreach ($this->normalize($node->$name) as $nnode) {
                    // we can do a text match on the token
                    if ($nnode instanceof Token && $matchNodeOrToken instanceof Token) {
                        $val1 = $nnode->getText($node->getFileContents());
                        $val2 = $matchNodeOrToken->getText($toMatch->getFileContents());
                        
                        if ($val1 !== $val2) {
                            return false;
                        }
                    }
                }
            }

            if (!$matchNodeOrToken instanceof Node) {
                continue;
            }

            $childNodeOrArray = $node->$name;
            if (is_array($childNodeOrArray)) {
                foreach ($childNodeOrArray as $childNode) {
                    if (false === $this->nodeMatches($childNode, $matchNodeOrToken)) {
                        return false;
                    }
                }

                continue;
            }

            if ($childNodeOrArray instanceof Node) {
                if (false === $this->nodeMatches($node->$name, $matchNodeOrToken)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param (Node|Token)|array<Node|Token> $nodeOrTokenOrArray
     * @return array<Node|Token>
     */
    private function normalize($nodeOrTokenOrArray): array
    {
        if (is_array($nodeOrTokenOrArray)) {
            return $nodeOrTokenOrArray;
        }
        return [$nodeOrTokenOrArray];
    }

    private function reducePattern(Node $patternNode): Node
    {
        foreach ($patternNode->getChildNodes() as $childNode) {
            if ($childNode instanceof InlineHtml) {
                continue;
            }

            if ($childNode instanceof ExpressionStatement) {
                $childNode = $childNode->expression;
            }

            return $childNode;
        }

        return $patternNode;
    }
}
