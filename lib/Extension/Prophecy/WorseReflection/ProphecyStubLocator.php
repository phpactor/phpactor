<?php

namespace Phpactor\Extension\Prophecy\WorseReflection;

use Phpactor\WorseReflection\Core\Name;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\SourceCodeLocator;
use Phpactor\WorseReflection\Core\SourceCodeLocator\InternalLocator;

class ProphecyStubLocator implements SourceCodeLocator
{
    private InternalLocator $locator;

    public function __construct()
    {
        $this->locator = new InternalLocator([
            'Prophecy\Prophecy\ObjectProphecy' => __DIR__ . '/../stubs/Prophecy.stub',
            'Prophecy\Prophecy\MethodProphecy' => __DIR__ . '/../stubs/Prophecy.stub'
        ]);
    }

    public function locate(Name $name): TextDocument
    {
        return $this->locator->locate($name);
    }
}
