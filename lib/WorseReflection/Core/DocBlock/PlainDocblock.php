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
use function preg_replace;

class PlainDocblock implements DocBlock
{
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
        $lines = [];
        foreach (explode("\n", $this->raw) as $line) {
            if (str_contains($line, '@')) {
                continue;
            }
            if (str_contains($line, '/*')) {
                continue;
            }
            if (str_contains($line, '*/')) {
                continue;
            }
            $line = trim(preg_replace('{\s+\*}', '', $line, 1));
            $lines[] = $line;
        }
        return trim(implode("\n", $lines));
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
