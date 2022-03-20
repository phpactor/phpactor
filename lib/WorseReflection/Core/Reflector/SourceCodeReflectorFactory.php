<?php

namespace Phpactor\WorseReflection\Core\Reflector;

use Phpactor\WorseReflection\Core\ServiceLocator;

interface SourceCodeReflectorFactory
{
    public function create(ServiceLocator $serviceLocator): SourceCodeReflector;
}
