<?php

namespace Phpactor\Configurator;

interface ChangeSuggestor
{
    public function suggestChanges(): Changes;
}
