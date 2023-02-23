<?php

namespace Phpactor\Configurator\Change;

use Phpactor\Configurator\Change;
use Phpactor\Configurator\ChangeApplicator;
use Phpactor\Configurator\ConfigManipulator;

class PhpactorConfigChangeApplicator implements ChangeApplicator
{
    public function __construct(private ConfigManipulator $maipulator)
    {
    }

    public function apply(Change $change): void
    {
        if (!$change instanceof PhpactorConfigChange) {
            return;
        }

        foreach ($change->keyValues() as $key => $value) {
            $this->maipulator->set($key, $value);
        }
    }
}
