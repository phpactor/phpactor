<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflector;

use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Parser\CachedParser;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflectorFactory;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Microsoft\PhpParser\Parser;

class TolerantFactory implements SourceCodeReflectorFactory
{
    public function __construct(private Parser $parser = new CachedParser())
    {
    }

    public function create(ServiceLocator $serviceLocator): SourceCodeReflector
    {
        return new TolerantSourceCodeReflector($serviceLocator, $this->parser);
    }
}
