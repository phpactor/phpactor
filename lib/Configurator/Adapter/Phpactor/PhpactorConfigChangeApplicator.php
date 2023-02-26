<?php

namespace Phpactor\Configurator\Adapter\Phpactor;

use Phpactor\Configurator\Model\Change;
use Phpactor\Configurator\Model\ChangeApplicator;
use Phpactor\Configurator\Model\ConfigManipulator;

class PhpactorConfigChangeApplicator implements ChangeApplicator
{
    public function __construct(private ConfigManipulator $maipulator)
    {
    }

    public function apply(Change $change): bool
    {
        if (!$change instanceof PhpactorConfigChange) {
            return false;
        }

        foreach ($change->keyValues() as $key => $value) {
            $this->maipulator->set($key, $value);
        }

        return true;
    }
}
