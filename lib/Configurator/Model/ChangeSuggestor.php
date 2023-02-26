<?php

namespace Phpactor\Configurator\Model;

interface ChangeSuggestor
{
    public function suggestChanges(): Changes;
}
