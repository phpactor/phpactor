<?php

namespace Phpactor\Completion\Bridge\WorseReflection\SnippetFormatter;

use Phpactor\Completion\Core\Formatter\Formatter;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;

class FunctionLikeSnippetFormatter implements Formatter
{
    public function canFormat(object $functionLike): bool
    {
        return $functionLike instanceof ReflectionFunction
            || $functionLike instanceof ReflectionMethod;
    }

    public function format(ObjectFormatter $formatter, object $functionLike): string
    {
        assert(
            $functionLike instanceof ReflectionFunction
            || $functionLike instanceof ReflectionMethod
        );

        $name = $functionLike instanceof ReflectionFunction
            ? $functionLike->name()->short()
            : $functionLike->name();
        $parameters = $functionLike->parameters();

        return $name . $formatter->format($parameters);
    }
}
