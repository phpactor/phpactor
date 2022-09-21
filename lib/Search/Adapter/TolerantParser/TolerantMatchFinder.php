<?php

namespace Phpactor\Search\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\MethodDeclaration;
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
use Phpactor\WorseReflection\Core\Util\NodeUtil;

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

        foreach ($template::CHILD_NAMES as $childName) {
            $nodeProps = $this->normalize($node->$childName);
            $templateProps = $this->normalize($template->$childName);

            $atLeastOne = $node instanceof ClassMembersNode && $childName === 'classMemberDeclarations';

            foreach ($templateProps as $index => $templateProp) {
                $nodeProp = $nodeProps[$index] ?? null;

                // template does not have property set, it's ok for node to have it
                if ($templateProp === null) {
                    continue;
                }


                if ($nodeProp instanceof Node && $templateProp instanceof Node) {
                    if (false === $this->nodeMatches($nodeProp, $templateProp, $matchTokens)) {
                        return false;
                    }
                }

                // out of range
                if (null === $nodeProp) {
                    return false;
                }

                if ($nodeProp instanceof Token && $templateProp instanceof Token) {
                    $match = $this->isMatch($template, $templateProp, $node, $nodeProp);

                    if ($match->isNo()) {
                        return false;
                    }

                    if ($match->isYes() && $match->name) {
                        if (!isset($matchTokens[$match->name])) {
                            $matchTokens[$match->name] = [];
                        }
                        $matchTokens[$match->name][] = $match->token;
                    }

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

    private function isMatch(Node $template, Token $matchNodeOrToken, Node $node, Token $nodeChild): MatchResult
    {
        $t1 = new MatchToken(
            ByteOffsetRange::fromInts($nodeChild->getStartPosition(), $nodeChild->getEndPosition()),
            (string)$nodeChild->getText($node->getFileContents()),
            $nodeChild->kind
        );

        $t2 = new MatchToken(
            ByteOffsetRange::fromInts($matchNodeOrToken->getStartPosition(), $matchNodeOrToken->getEndPosition()),
            (string)$matchNodeOrToken->getText($template->getFileContents()),
            $matchNodeOrToken->kind
        );

        $m  = $this->matcher->matches($t1, $t2);
        return $m;
    }
}
