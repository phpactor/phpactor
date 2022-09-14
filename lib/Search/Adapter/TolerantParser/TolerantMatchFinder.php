<?php

namespace Phpactor\Search\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Search\Model\MatchFinder;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\MatchTokens;
use Phpactor\Search\Model\Matcher;
use Phpactor\Search\Model\DocumentMatches;
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
    public function match(TextDocument $document, string $pattern): DocumentMatches
    {
        $patternNode = $this->parser->parseSourceFile('<?php ' . $pattern);
        $documentNode = $this->parser->parseSourceFile($document);
        $matches = [];
        $patternNode = $this->reducePattern($patternNode);
        $baseNodes = $this->findBaseNodes($documentNode, $patternNode);

        foreach ($baseNodes as $baseNode) {
            $matchTokens = [];
            if ($this->nodeMatches($baseNode, $patternNode, $matchTokens)) {
                $matches[] = new PatternMatch(
                    ByteOffsetRange::fromInts($baseNode->getStartPosition(), $baseNode->getEndPosition()),
                    new MatchTokens($matchTokens)
                );
            }
        }

        return new DocumentMatches($document, $matches);
    }

    /**
     * @return array<Node>
     * @param array<Node> $matches
     */
    private function findBaseNodes(Node $documentNode, Node $targetNode, array &$matches = []): array
    {
        foreach ($documentNode->getChildNodes() as $childNode) {
            if (get_class($childNode) === get_class($targetNode)) {
                $matches[] = $childNode;
            }

            $this->findBaseNodes($childNode, $targetNode, $matches);
        }

        return $matches;
    }


    /**
     * @param array<string,MatchToken> $matchTokens
     */
    private function nodeMatches(Node $node, Node $toMatch, array &$matchTokens): bool
    {
        foreach ($toMatch->getChildNodesAndTokens() as $name => $matchNodeOrToken) {

            // candidate does not have required token or node
            if (!isset($node->$name) || null === $node->$name) {
                return false;
            }

            // the `$node->$name` can either be an array of nodes or a node,
            // normalize to an array for simplicity
            $nodeChildren = $this->normalize($node->$name);

            // the subject matched up until here, but now the pattern specifies
            // something further that is not present in the subject.
            if (empty($nodeChildren)) {
                return false;
            }

            $matchedNode = null;
            foreach ($nodeChildren as $nodeChild) {

                // we only match tokens
                if (!$nodeChild instanceof Token || !$matchNodeOrToken instanceof Token) {
                    continue;
                }

                $t1 = new MatchToken(
                    ByteOffsetRange::fromInts($nodeChild->getStartPosition(), $nodeChild->getEndPosition()),
                    (string)$nodeChild->getText($node->getFileContents()),
                    $nodeChild->kind
                );

                $t2 = new MatchToken(
                    ByteOffsetRange::fromInts($matchNodeOrToken->getStartPosition(), $matchNodeOrToken->getEndPosition()),
                    (string)$matchNodeOrToken->getText($toMatch->getFileContents()),
                    $matchNodeOrToken->kind
                );

                $match = $this->matcher->matches($t1, $t2);

                // if it's a definite match, short cut
                if ($match->isYes()) {
                    $matchedNode = true;
                    if ($match->name) {
                        $matchTokens[$match->name] = $match->token;
                    } else {
                        $matchTokens[] = $match->token;
                    }
                    break;
                }

                // if it's not a match, allow further elements to match
                if ($match->isNo()) {
                    $matchedNode = false;
                }
            }

            if (false === $matchedNode) {
                return false;
            }

            if (!$matchNodeOrToken instanceof Node) {
                continue;
            }

            $matchedNode = false;
            foreach ($nodeChildren as $name => $nodeChild) {
                if (!$nodeChild instanceof Node) {
                    continue;
                }
                if ($this->nodeMatches($nodeChild, $matchNodeOrToken, $matchTokens)) {
                    $matchedNode = true;
                }
            }

            if ($matchedNode === false) {
                return false;
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
