<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Incremental;

use Microsoft\PhpParser\Node\SourceFileNode;

final class AstUpdaterResult
{
    public function __construct(
        public SourceFileNode $ast,
        public bool $success,
        public ?string $failureReason = null
    ) {
    }
}
