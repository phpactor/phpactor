<?php

namespace Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\WorseReflection\Reflector;

class NameSearchResultFunctionSnippetFormatter implements Formatter
{
    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function canFormat(object $object): bool
    {
        return $object instanceof NameSearchResult
            && $object->type()->isFunction();
    }

    /**
     * @param ObjectFormatter $formatter
     * @param object $nameSearchResult
     * @return string
     */
    public function format(ObjectFormatter $formatter, object $nameSearchResult): string
    {
        assert($nameSearchResult instanceof NameSearchResult);
        $functionName = $nameSearchResult->name()->__toString();
        $functionReflection = $this->reflector->reflectFunction($functionName);
        return $formatter->format($functionReflection);
    }
}
