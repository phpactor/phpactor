<?php

namespace Phpactor\Complete;

use Phpactor\Complete\CompleteContext;
use Phpactor\Complete\Suggestions;

interface ProviderInterface
{
    public function canProvideFor(CompleteContext $context): bool;

    public function provide(CompleteContext $context, Suggestions $suggestions);
}
