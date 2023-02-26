<?php

namespace Phpactor\Configurator\Model;

interface ChangeApplicator
{
    /**
     * Return true if the applicator accepted the change.
     * Note that acceptance means consuming - no other applicators
     * will be called for the provided change.
     */
    public function apply(Change $change): bool;
}
