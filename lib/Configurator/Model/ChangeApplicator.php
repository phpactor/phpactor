<?php

namespace Phpactor\Configurator\Model;

use Phpactor\Configurator\Model\Change;

interface ChangeApplicator
{
    public function apply(Change $change): void;
}
