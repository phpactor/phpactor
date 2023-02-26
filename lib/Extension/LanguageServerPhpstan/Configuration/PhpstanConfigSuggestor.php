<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Configuration;

use Composer\Autoload\ClassLoader;
use Phpactor\Configurator\ChangeSuggestor;
use Phpactor\Configurator\Model\Changes;
use Phpactor\Configurator\Model\JsonConfig;
use Phpactor\Extension\LanguageServerPhpstan\LanguageServerPhpstanExtension;

class PhpstanConfigSuggestor implements ChangeSuggestor
{
    public function __construct(private JsonConfig $phpactorConfig, ClassLoader $classLoader)
    {
    }

    public function suggestChanges(): Changes
    {
        if ($this->phpactorConfig->has(LanguageServerPhpstanExtension::PARAM_ENABLED)) {
            return new Changes([]);
        }

        return new Changes([]);
    }
}
