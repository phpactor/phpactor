<?php

namespace Phpactor\Complete;

use Phpactor\Complete\CompleteContext;
use Phpactor\Complete\Suggestions;
use Phpactor\Complete\Scope;

interface ProviderInterface
{
    public function canProvideFor(Scope $scope): bool;

    public function provide(Scope $scope, Suggestions $suggestions);
}
