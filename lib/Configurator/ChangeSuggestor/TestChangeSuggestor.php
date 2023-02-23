<?php

namespace Phpactor\Configurator\ChangeSuggestor;

use Closure;
use Phpactor\Configurator\ChangeSuggestor;
use Phpactor\Configurator\Changes;

class TestChangeSuggestor implements ChangeSuggestor
{
    /**
     * @param Closure(): Changes $closure
     */
    public function __construct(private Closure $closure)
    {
    }

    public function suggestChanges(): Changes
    {
        return ($this->closure)();
    }
}
