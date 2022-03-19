<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection;

use Phpactor\CodeTransform\Tests\Adapter\AdapterTestCase;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Adapter\WorseReflection\WorseBuilderFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseTestCase extends AdapterTestCase
{
    public function reflectorForWorkspace($source = null): Reflector
    {
        $builder = ReflectorBuilder::create();

        foreach ((array)glob($this->workspace()->path('/*.php')) as $file) {
            $locator = new TemporarySourceLocator(ReflectorBuilder::create()->build(), true);
            $locator->pushSourceCode(SourceCode::fromPathAndString($file, file_get_contents($file)));
            $builder->addLocator($locator);
        }

        if ($source) {
            $builder->addSource($source);
        }

        return $builder->build();
    }

    public function builderFactory(Reflector $reflector): BuilderFactory
    {
        return new WorseBuilderFactory($reflector);
    }
}
