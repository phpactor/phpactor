<?php

namespace Phpactor\Configurator\Change;

use Phpactor\Configurator\Model\Change;
use Phpactor\Configurator\Model\ChangeApplicator;
use Phpactor\Configurator\Model\ConfigManipulator;

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
