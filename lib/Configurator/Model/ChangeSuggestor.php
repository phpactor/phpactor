<?php

namespace Phpactor\Configurator\Model;

use Phpactor\Configurator\Model\Changes;

interface ChangeSuggestor
{
    public function suggestChanges(): Changes;
}
