<?php

namespace Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\ReferenceFinder\Search\NameSearchResult;
use Phpactor\WorseReflection\Reflector;

class NameSearchResultClassSnippetFormatter implements Formatter
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
            && $object->type()->isClass();
    }

    /**
     * @param ObjectFormatter $formatter
     * @param NameSearchResult $nameSearchResult
     * @return string
     */
    public function format(ObjectFormatter $formatter, object $nameSearchResult): string
    {
        assert($nameSearchResult instanceof NameSearchResult);
        $className = $nameSearchResult->name()->__toString();

        $classReflection = $this->reflector->reflectClassLike($className);
        $shortName = $classReflection->name()->short();

        if ($classReflection->methods()->has('__construct') === false) {
            return $shortName . '()';
        }

        $constructorReflection = $classReflection->methods()->get('__construct');
        $parameters = $constructorReflection->parameters();
        return $shortName . $formatter->format($parameters);
    }
}
