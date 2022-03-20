<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\ScriptInclusionExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\FrameWalker;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Psr\Log\LoggerInterface;
use Webmozart\PathUtil\Path;

class IncludeWalker implements FrameWalker
{
    private Parser $parser;
    
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
        $this->logger = $logger;
    }

    public function canWalk(Node $node): bool
    {
        return $node instanceof ScriptInclusionExpression;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        assert($node instanceof ScriptInclusionExpression);
        $context = $builder->resolveNode($frame, $node->expression);
        $includeUri = $context->value();

        if (!is_string($includeUri)) {
            return $frame;
        }

        $sourceNode = $node->getFirstAncestor(SourceFileNode::class);
        assert($sourceNode instanceof SourceFileNode);

        if (!$sourceNode) {
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

        $sourceNode = $this->parser->parseSourceFile(file_get_contents($includeUri));
        $includedFrame = $builder->build($sourceNode);

        $parentNode = $node->parent;

        if ($parentNode instanceof AssignmentExpression) {
            return $this->processAssignment($sourceNode, $builder, $frame, $parentNode, $node);
        }

        $frame->locals()->merge($includedFrame->locals());

        return $frame;
    }

    private function processAssignment(SourceFileNode $sourceNode, FrameBuilder $builder, Frame $frame, AssignmentExpression $parentNode, ScriptInclusionExpression $node)
    {
        $return = $sourceNode->getFirstDescendantNode(ReturnStatement::class);
        assert($return instanceof ReturnStatement);
        $returnValueContext = $builder->resolveNode($frame->new('required'), $return->expression);
        
        if (!$parentNode->leftOperand instanceof Variable) {
            return $frame;
        }
        
        $name = $parentNode->leftOperand->name;
        
        if (!$name instanceof Token) {
            return $frame;
        }
        
        $name = $name->getText($node->getFileContents());
        
        /** @var WorseVariable $variable */
        foreach ($frame->locals()->byName($name) as $variable) {
            $frame->locals()->add(
                $variable->withTypes($returnValueContext->types())
            );
            return $frame;
        }
    }
}
