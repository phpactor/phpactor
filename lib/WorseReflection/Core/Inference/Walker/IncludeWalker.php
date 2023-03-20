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
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\TypeUtil;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Path;

class IncludeWalker implements Walker
{
    private Parser $parser;

    public function __construct(private LoggerInterface $logger, Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
    }


    public function nodeFqns(): array
    {
        return [ScriptInclusionExpression::class];
    }

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        assert($node instanceof ScriptInclusionExpression);
        $context = $resolver->resolveNode($frame, $node->expression);
        $includeUri = TypeUtil::valueOrNull($context->type());

        if (!is_string($includeUri)) {
            return;
        }

        $sourceNode = $node->getFirstAncestor(SourceFileNode::class);

        if (!$sourceNode instanceof SourceFileNode) {
            return;
        }

        $uri = $sourceNode->uri;

        if (!$uri) {
            $this->logger->warning('source code has no path associated with it, cannot process include');
            return;
        }

        if (Path::isRelative($includeUri)) {
            $includeUri = Path::join(dirname($uri), $includeUri);
        }

        if (!file_exists($includeUri)) {
            $this->logger->warning('require/include "%s" does not exist');
            return;
        }

        $sourceNode = $this->parser->parseSourceFile((string)file_get_contents($includeUri));
        $includedFrame = $resolver->build($sourceNode);

        $parentNode = $node->parent;

        if ($parentNode instanceof AssignmentExpression) {
            $this->processAssignment($sourceNode, $resolver, $frameStack, $parentNode, $node);
            return;
        }

        $frameStack->current()->locals()->merge($includedFrame->locals());

        return;
    }

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        return;
    }

    private function processAssignment(SourceFileNode $sourceNode, FrameResolver $resolver, FrameStack $frameStack, AssignmentExpression $parentNode, ScriptInclusionExpression $node): void
    {
        $return = $sourceNode->getFirstDescendantNode(ReturnStatement::class);
        assert($return instanceof ReturnStatement);
        $frameStack->newFrame();
        if (!$return->expression) {
            return;
        }
        $returnValueContext = $resolver->resolveNode($frameStack, $return->expression);
        $frameStack->popFrame();

        if (!$parentNode->leftOperand instanceof Variable) {
            return;
        }

        $name = $parentNode->leftOperand->name;

        if (!$name instanceof Token) {
            return;
        }

        $name = $name->getText($node->getFileContents());

        foreach ($frameStack->current()->locals()->byName((string)$name) as $variable) {
            $frameStack->current()->locals()->replace(
                $variable,
                $variable->withType($returnValueContext->type())
            );
            return;
        }

        return;
    }
}
