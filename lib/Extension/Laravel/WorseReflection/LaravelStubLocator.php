<?php

namespace Phpactor\Extension\Laravel\WorseReflection;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\InternalLocator;

class LaravelStubLocator implements SourceCodeLocator
{
    private InternalLocator $locator;

    public function __construct()
    {
        $this->locator = new InternalLocator([
            'LaravelHasManyVirtualBuilder' => __DIR__ . '/../stubs/LaravelRelationBuilderStub.php',
            'LaravelBelongsToVirtualBuilder' => __DIR__ . '/../stubs/LaravelRelationBuilderStub.php',
            'LaravelBelongsToManyVirtualBuilder' => __DIR__ . '/../stubs/LaravelRelationBuilderStub.php',
            'LaravelQueryVirtualBuilder' => __DIR__ . '/../stubs/LaravelRelationBuilderStub.php',
        ]);
    }

    public function locate(Name $name): TextDocument
    {
        return $this->locator->locate($name);
    }
}
