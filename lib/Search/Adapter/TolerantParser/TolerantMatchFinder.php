<?php

namespace Phpactor\Search\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Search\Model\MatchFinder;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\Matcher;
use Phpactor\Search\Model\Matches;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;

class TolerantMatchFinder implements MatchFinder
{
    private Parser $parser;
    private Matcher $matcher;

    public function __construct(Parser $parser, Matcher $matcher)
    {
        $this->parser = $parser;
        $this->matcher = $matcher;
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
            // candidate does not have required token or node
            if (!isset($node->$name) || null === $node->$name) {
                return false;
            }

            foreach ($this->normalize($node->$name) as $nnode) {

                // we only match tokens
                if (!$nnode instanceof Token || !$matchNodeOrToken instanceof Token) {
                    continue;
                }

                $t1 = new MatchToken(
                    ByteOffsetRange::fromInts($nnode->getStartPosition(), $nnode->getEndPosition()),
                    (string)$nnode->getText($node->getFileContents()),
                    $nnode->kind
                );

                $t2 = new MatchToken(
                    ByteOffsetRange::fromInts($matchNodeOrToken->getStartPosition(), $matchNodeOrToken->getEndPosition()),
                    (string)$matchNodeOrToken->getText($toMatch->getFileContents()),
                    $matchNodeOrToken->kind
                );

                if ($this->matcher->matches($t1, $t2)->isNotMatch()) {
                    return false;
                }
            }

            if (!$matchNodeOrToken instanceof Node) {
                continue;
            }

            foreach ($this->normalize($node->$name) as $normal) {
                if (!$normal instanceof Node) {
                    continue;
                }
                if (false === $this->nodeMatches($normal, $matchNodeOrToken)) {
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

    /**
     * Remove any unnecessary preceding nodes from the pattern AST (e.g. HTML,
     * Statement)
     */
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
