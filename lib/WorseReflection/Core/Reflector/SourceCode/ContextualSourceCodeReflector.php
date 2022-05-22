<?php

namespace Phpactor\WorseReflection\Core\Reflector\SourceCode;

use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionNavigation;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionFunctionCollection;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethodCall;
use Phpactor\WorseReflection\Core\SourceCodeLocator\TemporarySourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionClassCollection;

class ContextualSourceCodeReflector implements SourceCodeReflector
{
    private SourceCodeReflector $innerReflector;
    
    private TemporarySourceLocator $locator;

    public function __construct(SourceCodeReflector $innerReflector, TemporarySourceLocator $locator)
    {
        $this->innerReflector = $innerReflector;
        $this->locator = $locator;
    }
    
    public function reflectClassesIn($sourceCode): ReflectionClassCollection
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $this->locator->pushSourceCode($sourceCode);

        $collection = $this->innerReflector->reflectClassesIn($sourceCode);

        return $collection;
    }
    
    public function reflectOffset($sourceCode, $offset): ReflectionOffset
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $this->locator->pushSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectOffset($sourceCode, $offset);

        return $offset;
    }

    public function reflectMethodCall($sourceCode, $offset): ReflectionMethodCall
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $this->locator->pushSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectMethodCall($sourceCode, $offset);

        return $offset;
    }
    
    public function reflectFunctionsIn($sourceCode): ReflectionFunctionCollection
    {
        $sourceCode = SourceCode::fromUnknown($sourceCode);
        $this->locator->pushSourceCode($sourceCode);

        $offset = $this->innerReflector->reflectFunctionsIn($sourceCode);

        return $offset;
    }

    public function navigate($sourceCode): ReflectionNavigation
    {
        return $this->innerReflector->navigate($sourceCode);
    }
}
