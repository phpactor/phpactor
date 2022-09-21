<?php

namespace Phpactor\Search\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\Search\Adapter\TolerantParser\Matcher\TokenEqualityMatcher;
use Phpactor\Search\Model\MatchFinder;
use Phpactor\Search\Model\MatchResult;
use Phpactor\Search\Model\MatchToken;
use Phpactor\Search\Model\MatchTokens;
use Phpactor\Search\Model\Matcher;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\Matcher\ChainMatcher;
use Phpactor\Search\Model\Matcher\PlaceholderMatcher;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocument;

/**
 * This finder will match all parts of the documents AST which match the
 * template AST.
 *
 * The finder works:
 *
 * - Parse the template string, obtaining a template AST
 * - Parse the document
 * - Find all nodes in the document having the same type as the template AST
 * - For each of those nodes
 *   - Check it is the same class as the template node
 *   - Check to see if tokens in the node match the tokens on the template node
 *   - Descend into any children of the node, passing the corresponding child of the template node and recurse.
 */
class TolerantMatchFinder implements MatchFinder
{
    private Parser $parser;

    private Matcher $matcher;

    public function __construct(Parser $parser, Matcher $matcher)
    {
        $this->parser = $parser;
        $this->matcher = $matcher;
    }

    public static function createDefault(): self
    {
        return new self(
            new Parser(),
            new ChainMatcher(
                new PlaceholderMatcher(),
                new TokenEqualityMatcher()
            )
        );
    }

    public function match(TextDocument $document, string $template): DocumentMatches
    {
        $templateNode = $this->parser->parseSourceFile('<?php ' . $template);
        $documentNode = $this->parser->parseSourceFile($document);
        $matches = [];
        $templateNode = $this->reducePattern($templateNode);
        $baseNodes = $this->findBaseNodes($documentNode, $templateNode);

        foreach ($baseNodes as $baseNode) {
            $matchTokens = [];
            if ($this->nodeMatches($baseNode, $templateNode, $matchTokens)) {
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
    private function findBaseNodes(Node $documentNode, Node $templateNode, array &$matches = []): array
    {
        foreach ($documentNode->getChildNodes() as $childNode) {
            if (get_class($childNode) === get_class($templateNode)) {
                $matches[] = $childNode;
            }

            $this->findBaseNodes($childNode, $templateNode, $matches);
        }

        return $matches;
    }


    /**
     * @param array<string,MatchToken> $matchTokens
     */
    private function nodeMatches(Node $node, Node $template, array &$matchTokens): bool
    {
        if (get_class($node) !== get_class($template)) {
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
     * Remove any unnecessary preceding nodes from the template AST (e.g. HTML,
     * Statement)
     */
    private function reducePattern(Node $templateNode): Node
    {
        foreach ($templateNode->getChildNodes() as $childNode) {
            if ($childNode instanceof InlineHtml) {
                continue;
            }

            if ($childNode instanceof ExpressionStatement) {
                $childNode = $childNode->expression;
            }

            return $childNode;
        }

        return $templateNode;
    }

    private function isMatch(Node $template, Token $templateToken, Node $node, Token $nodeToken): MatchResult
    {
        $t1 = new MatchToken(
            ByteOffsetRange::fromInts($nodeToken->getStartPosition(), $nodeToken->getEndPosition()),
            (string)$nodeToken->getText($node->getFileContents()),
            $nodeToken->kind
        );

        $t2 = new MatchToken(
            ByteOffsetRange::fromInts($templateToken->getStartPosition(), $templateToken->getEndPosition()),
            (string)$templateToken->getText($template->getFileContents()),
            $templateToken->kind
        );

        return $this->matcher->matches($t1, $t2);
    }

    /**
     * @param Node|Token|null $templateNodeOrToken
     * @param Node|Token|null $nodeOrToken
     * @param array<string,MatchToken[]> $matchTokens
     */
    private function nodesMatch(Node $template, $templateNodeOrToken, Node $node, $nodeOrToken, array &$matchTokens): bool
    {
        // template does not have property set, it's ok for node to have it
        if ($templateNodeOrToken === null) {
            return true;
        }

        // @phpstan-ignore-next-line TP lies - out of range
        if (null === $node) {
            return false;
        }

        if ($nodeOrToken instanceof Node && $templateNodeOrToken instanceof Node) {
            return $this->nodeMatches($nodeOrToken, $templateNodeOrToken, $matchTokens);
        }

        if ($nodeOrToken instanceof Token && $templateNodeOrToken instanceof Token) {
            $match = $this->isMatch($template, $templateNodeOrToken, $node, $nodeOrToken);

            if ($match->isNo()) {
                return false;
            }

            if ($match->name) {
                if (!isset($matchTokens[$match->name])) {
                    $matchTokens[$match->name] = [];
                }
                $matchTokens[$match->name][] = $match->token;
            }

            return true;
        }

        return false;
    }

    private function shouldMatchOneOf(Node $node, string $childName): bool
    {
        return $node instanceof ClassMembersNode && $childName === 'classMemberDeclarations';
    }
}
