<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Configuration;

use Phpactor\ComposerInspector\ComposerInspector;
use Phpactor\Configurator\Adapter\Phpactor\PhpactorConfigChange;
use Phpactor\Configurator\Model\ChangeSuggestor;
use Phpactor\Configurator\Model\Changes;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Extension\LanguageServerPhpstan\LanguageServerPhpstanExtension;

class PhpstanConfigSuggestor implements ChangeSuggestor
{
    public function __construct(private JsonConfig $phpactorConfig, private ComposerInspector $composerInspector)
    {
    }

    public function suggestChanges(): Changes
    {
        if ($this->phpactorConfig->has(LanguageServerPhpstanExtension::PARAM_ENABLED)) {
            return Changes::none();
        }

        if (!$this->composerInspector->package('phpstan/phpstan')) {
            return Changes::none();
        }

        return Changes::from([
            new PhpactorConfigChange('Phpstan detected, enable PHPStan?', [
                LanguageServerPhpstanExtension::PARAM_ENABLED => true,
            ])
        ]);
    }
}
