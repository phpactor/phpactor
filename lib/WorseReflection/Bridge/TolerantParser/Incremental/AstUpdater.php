<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Incremental;

use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\Strategy\CompoundNodeStrategy;
use Phpactor\WorseReflection\Bridge\TolerantParser\Incremental\Strategy\TokenStrategy;
use Phpactor\WorseReflection\Core\AstProvider;
use Throwable;

final class AstUpdater
{
    public function __construct(
        private SourceFileNode $node,
        private AstProvider $astProvider = new TolerantAstProvider(),
        /** @var UpdaterStrategy[] */
        private array $strategies = [],
    ) {
    }

    public static function create(SourceFileNode $node, AstProvider $provider = new TolerantAstProvider()): self
    {
        return new self($node, $provider, [
            new TokenStrategy(),
            new CompoundNodeStrategy(),
        ]);
    }

    public function apply(TextEdit $edit, TextDocumentUri $uri): AstUpdaterResult
    {
        $ast = $this->node;
        $node = $ast->getDescendantNodeAtPosition($edit->start()->toInt());
        $updatedSource = TextEdits::one($edit)->apply($this->node->getFileContents());
        $tried = [];

        foreach ($this->strategies as $strategy) {
            try {
                $result = $strategy->apply($node, $edit);
            } catch (Throwable $e) {
                $result = new OperationResult('exception', false, $e->getMessage());
            }

            if ($result->success === true) {
                $ast->fileContents = $updatedSource;
                return new AstUpdaterResult($ast, true, $result->name);
            }

            $tried[] = $result;
        }

        $failureReason = implode(', ', array_map(function (OperationResult $result) {
            return sprintf('[%s] %s', $result->name, $result->reason);
        }, $tried));

        return new AstUpdaterResult($this->astProvider->get(
            TextDocumentBuilder::create($updatedSource)->uri($uri)->build()
        ), false, $failureReason);
    }
}
