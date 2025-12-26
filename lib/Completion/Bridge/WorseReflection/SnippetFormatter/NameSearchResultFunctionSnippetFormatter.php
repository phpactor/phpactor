<?php

namespace Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\WorseReflection\Reflector;

class NameSearchResultFunctionSnippetFormatter implements Formatter
{
    public function __construct(private readonly Reflector $reflector)
    {
    }

    public function canFormat(object $object): bool
    {
        return $object instanceof NameSearchResult
            && $object->type()->isFunction();
    }


    public function format(ObjectFormatter $formatter, object $nameSearchResult): string
    {
        assert($nameSearchResult instanceof NameSearchResult);
        $functionName = $nameSearchResult->name()->__toString();
        $functionReflection = $this->reflector->reflectFunction($functionName);
        return $formatter->format($functionReflection);
    }
}
