<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider;

use Microsoft\PhpParser\Node\SourceFileNode;

final class IncrementalAstUpdaterResult
{
    public function __construct(
        public SourceFileNode $ast,
        public bool $success,
        public ?string $failureReason = null
    ) {
    }
}
