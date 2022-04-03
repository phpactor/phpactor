<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\ScriptInclusionExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Psr\Log\LoggerInterface;
use Webmozart\PathUtil\Path;

class IncludeWalker implements Walker
{
    private Parser $parser;
    
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->logger = $logger;
    }

    
    public function nodeFqns(): array
    {
        return [ScriptInclusionExpression::class];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof ScriptInclusionExpression);
        $context = $resolver->resolveNode($frame, $node->expression);
        $includeUri = $context->value();

        if (!is_string($includeUri)) {
            return $frame;
        }

        $sourceNode = $node->getFirstAncestor(SourceFileNode::class);

        if (!$sourceNode instanceof SourceFileNode) {
            return $frame;
        }

        $uri = $sourceNode->uri;

        if (!$uri) {
            $this->logger->warning('source code has no path associated with it, cannot process include');
            return $frame;
        }

        if (Path::isRelative($includeUri)) {
            $includeUri = Path::join([dirname($uri), $includeUri]);
        }

        if (!file_exists($includeUri)) {
            $this->logger->warning('require/include "%s" does not exist');
            return $frame;
        }

        $sourceNode = $this->parser->parseSourceFile((string)file_get_contents($includeUri));
        $includedFrame = $resolver->build($sourceNode);

        $parentNode = $node->parent;

        if ($parentNode instanceof AssignmentExpression) {
            return $this->processAssignment($sourceNode, $resolver, $frame, $parentNode, $node);
        }

        $frame->locals()->merge($includedFrame->locals());

        return $frame;
    }

    private function processAssignment(SourceFileNode $sourceNode, FrameResolver $resolver, Frame $frame, AssignmentExpression $parentNode, ScriptInclusionExpression $node): Frame
    {
        $return = $sourceNode->getFirstDescendantNode(ReturnStatement::class);
        assert($return instanceof ReturnStatement);
        $returnValueContext = $resolver->resolveNode($frame->new('required'), $return->expression);
        
        if (!$parentNode->leftOperand instanceof Variable) {
            return $frame;
        }
        
        $name = $parentNode->leftOperand->name;
        
        if (!$name instanceof Token) {
            return $frame;
        }
        
        $name = $name->getText($node->getFileContents());
        
        foreach ($frame->locals()->byName((string)$name) as $variable) {
            $frame->locals()->replace(
                $variable,
                $variable->withTypes($returnValueContext->types())
            );
            return $frame;
        }

        return $frame;
    }
}
