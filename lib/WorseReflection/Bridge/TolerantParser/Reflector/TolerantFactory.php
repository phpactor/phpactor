<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflector;

use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\CachedAstProvider;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflectorFactory;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\ServiceLocator;

class TolerantFactory implements SourceCodeReflectorFactory
{
    public function __construct(private AstProvider $parser = new CachedAstProvider())
    {
    }

    public function create(ServiceLocator $serviceLocator): SourceCodeReflector
    {
        return new TolerantSourceCodeReflector($serviceLocator, $this->parser);
    }
}
