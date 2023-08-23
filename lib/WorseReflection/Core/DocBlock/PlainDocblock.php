<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\TypeResolver;

class PlainDocblock implements DocBlock
{
    private const LEADING = 3;
    private const TEXT = 2;
    private const EXTRA_ASTERIX = 1;
    private const START = 0;
    private const WHITESPACE = 4;
    private const WS_OR_TERMINATE = 5;

    private string $raw;

    public function __construct(string $raw = '')
    {
        $this->raw = $raw;
    }

    public function methodType(string $methodName): Type
    {
        return TypeFactory::undefined();
    }

    public function inherits(): bool
    {
        return str_contains($this->raw, '@inheritDoc');
    }

    public function vars(): DocBlockVars
    {
        return new DocBlockVars([]);
    }

    public function params(): DocBlockParams
    {
        return new DocBlockParams([]);
    }

    public function parameterType(string $paramName): Type
    {
        return TypeFactory::undefined();
    }

    public function propertyType(string $methodName): Type
    {
        return TypeFactory::undefined();
    }

    public function formatted(): string
    {
        $mode = self::START;
        $buffer = '';
        $text = '';
        foreach (str_split(trim($this->raw)) as $char) {
            $buffer .= $char;

            switch ($mode) {
                case self::START:
                    if ($buffer === '/*') {
                        $mode = self::EXTRA_ASTERIX;
                        $buffer = '';
                    }
                    break;
                case self::EXTRA_ASTERIX:
                    if ($buffer === '*') {
                        $buffer = '';
                    }
                    $mode = self::TEXT;
                    break;
                case self::TEXT:
                    if ($char === "\n") {
                        $text .= "\n";
                        $mode = self::LEADING;
                        break;
                    }
                    if ($char === '*') {
                        $mode = self::WS_OR_TERMINATE;
                        break;
                    }
                    $text .= $char;
                    break;
                case self::LEADING:
                    if ($char === '*') {
                        $mode = self::WS_OR_TERMINATE;
                    }
                    break;
                case self::WHITESPACE:
                    if ($char !== ' ') {
                        $mode = self::TEXT;
                        $text .= $char;
                    }
                    break;
                case self::WS_OR_TERMINATE:
                    if ($char === '/') {
                        return trim($text);
                    }

                    if ($char !== ' ') {
                        $text = trim($text, ' ') . $char;
                        $mode = self::TEXT;
                        break;
                    }
                    break;
            }
        }

        return $text;
    }

    public function returnType(): Type
    {
        return TypeFactory::undefined();
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function isDefined(): bool
    {
        return $this->raw !== '';
    }

    public function properties(ReflectionClassLike $declaringClass): CoreReflectionPropertyCollection
    {
        return ReflectionPropertyCollection::empty();
    }

    public function methods(ReflectionClassLike $declaringClass): CoreReflectionMethodCollection
    {
        return ReflectionMethodCollection::empty();
    }

    public function deprecation(): Deprecation
    {
        return new Deprecation(false);
    }

    public function templateMap(): TemplateMap
    {
        return new TemplateMap([]);
    }

    public function extends(): array
    {
        return [];
    }

    public function implements(): array
    {
        return [];
    }

    public function mixins(): array
    {
        return [];
    }

    public function withTypeResolver(TypeResolver $classLikeTypeResolver): DocBlock
    {
        return $this;
    }
}
