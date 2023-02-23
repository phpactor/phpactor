<?php

namespace Phpactor\Configurator;

interface ChangeApplicator
{
    public function apply(Change $change): void;
}
