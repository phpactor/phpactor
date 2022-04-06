<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Virtual\Collection\VirtualReflectionPropertyCollection;
use function preg_replace;

class PlainDocblock implements DocBlock
{
    private string $raw;

    public function __construct(string $raw = '')
    {
        $this->raw = trim($raw);
    }

    public function methodType(string $methodName): Type
    {
        return TypeFactory::undefined();
    }

    public function inherits(): bool
    {
        return false !== strpos($this->raw, '@inheritDoc');
    }

    public function vars(): DocBlockVars
    {
        return new DocBlockVars([]);
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
            if (false !== strpos($line, '@')) {
                continue;
            }
            if (false !== strpos($line, '/*')) {
                continue;
            }
            if (false !== strpos($line, '*/')) {
                continue;
            }
            $line = trim(preg_replace('{\s+\*}', '', $line));
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

    public function properties(ReflectionClassLike $declaringClass): ReflectionPropertyCollection
    {
        return VirtualReflectionPropertyCollection::fromReflectionProperties([]);
    }

    public function methods(ReflectionClassLike $declaringClass): ReflectionMethodCollection
    {
        return VirtualReflectionMethodCollection::fromReflectionMethods([]);
    }

    public function deprecation(): Deprecation
    {
        return new Deprecation(false);
    }

    public function templateMap(): TemplateMap
    {
        return new TemplateMap([]);
    }

    public function extends(): Type
    {
        return new MissingType();
    }

    public function implements(): array
    {
        return [];
    }
}
