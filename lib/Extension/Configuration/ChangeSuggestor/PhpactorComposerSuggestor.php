<?php

namespace Phpactor\Extension\Configuration\ChangeSuggestor;

use Closure;
use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Configurator\Model\ChangeSuggestor;
use Phpactor\Configurator\Model\Changes;
use Phpactor\Configurator\Model\JsonConfig;

class PhpactorComposerSuggestor implements ChangeSuggestor
{
    /**
     * @param Closure(JsonConfig, ComposerInspector): Changes $suggestor
     */
    public function __construct(
        private JsonConfig $phpactorConfig,
        private ComposerInspector $composerInspector,
        private Closure $suggestor,
    ) {
    }

    public function suggestChanges(): Changes
    {
        return ($this->suggestor)($this->phpactorConfig, $this->composerInspector);
    }
}
