<?php

namespace Phpactor\Application;

use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

class Complete
{
    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var FilesystemHelper
     */
    private $filesystemHelper;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
        $this->filesystemHelper = new FilesystemHelper();
    }

    public function complete(string $source, int $offset): array
    {
        list($offset, $partialMatch) = $this->getOffetToReflect($source, $offset);

        $reflectionOffset = $this->reflector->reflectOffset(
            SourceCode::fromString($source),
            Offset::fromint($offset)
        );

        $symbolInformation = $reflectionOffset->symbolInformation();
        $types = $symbolInformation->types();

        $suggestions = [];

        foreach ($types as $type) {
            $symbolInformation = $this->populateSuggestions($symbolInformation, $type, $suggestions);
        }

        return [
            'suggestions' => array_values($suggestions),
            'issues' => $symbolInformation->issues(),
        ];

    }

    private function getOffetToReflect($source, $offset)
    {
        $source = str_replace(PHP_EOL, ' ', $source);
        $untilCursor = substr($source, 0, $offset);

        $pos = strlen($untilCursor) - 1;
        $original = null;
        while ($pos) {
            if (in_array(substr($untilCursor, $pos, 2), [ '->', '::' ])) {
                $original = $pos;
                break;
            }
            $pos--;
        }

        $pos--;
        while (isset($untilCursor[$pos]) && $untilCursor[$pos] == ' ') {
            $pos--;
        }
        $pos++;

        $accessorOffset = ($original - $pos) + 2;
        $extra = substr($untilCursor, $pos + $accessorOffset, $offset);

        return [ $pos,  $extra ];
    }

    private function getMethodInfo(ReflectionMethod $method)
    {
        $info = [
            substr((string) $method->visibility(), 0, 3),
            ' ',
            $method->name()
        ];

        if ($method->isAbstract()) {
            array_unshift($info, 'abstract ');
        }

        $paramInfos = [];

        /** @var $parameter ReflectionParameter */
        foreach ($method->parameters() as $parameter) {
            $paramInfo = [];
            if ($parameter->type()->isDefined()) {
                $paramInfo[] = $parameter->type()->short();
            }
            $paramInfo[] = '$' . $parameter->name();

            if ($parameter->default()->isDefined()) {
                $paramInfo[] = '= '. str_replace(PHP_EOL, '', var_export($parameter->default()->value(), true));
            }
            $paramInfos[] = implode(' ', $paramInfo);
        }
        $info[] = '(' . implode(', ', $paramInfos) . ')';

        $returnTypes = $method->inferredReturnTypes();
        if ($returnTypes->count() > 0) {
            $info[] = ': ' . implode('|', array_map(function (Type $type) {
                return $type->short();
            }, iterator_to_array($returnTypes)));
        }

        return implode('', $info);
    }

    private function getPropertyInfo(ReflectionProperty $property)
    {
        $info = [
            substr((string) $property->visibility(), 0, 3),
        ];

        if ($property->isStatic()) {
            $info[] = ' static';
        }

        $info[] = ' ';
        $info[] = '$' . $property->name();

        if ($property->inferredTypes()->best()->isDefined()) {
            $info[] = ': ' . (string) $property->inferredTypes()->best()->short();
        }

        return implode('', $info);
    }

    private function populateSuggestions(SymbolContext $symbolInformation, Type $type, array &$suggestions)
    {
        if (false === $type->isDefined()) {
            return $symbolInformation;
        }

        if ($type->isPrimitive()) {
            return $symbolInformation->withIssue(sprintf('Cannot complete members on scalar value (%s)', (string) $type));
        }

        try {
            $classReflection = $this->reflector->reflectClassLike(ClassName::fromString((string) $type));
        } catch (NotFound $e) {
            return $symbolInformation->withIssue(sprintf('Could not find class "%s"', (string) $type));
        }

        $publicOnly = !in_array($symbolInformation->symbol()->name(), ['this', 'self'], true);
        /** @var $method ReflectionMethod */
        foreach ($classReflection->methods() as $method) {
            if ($method->name() === '__construct') {
                continue;
            }
            if ($publicOnly && false === $method->visibility()->isPublic()) {
                continue;
            }
            $info = $this->getMethodInfo($method);
            $suggestions[] = [
                'type' => 'f',
                'name' => $method->name(),
                'info' => $info,
            ];
        }

        if ($classReflection instanceof ReflectionClass) {
            foreach ($classReflection->properties() as $property) {
                if ($publicOnly && false === $property->visibility()->isPublic()) {
                    continue;
                }
                $suggestions[] = [
                    'type' => 'm',
                    'name' => $property->name(),
                    'info' => $this->getPropertyInfo($property),
                ];
            }
        }

        foreach ($classReflection->constants() as $constant) {
            $suggestions[] = [
                'type' => 'm',
                'name' => $constant->name(),
                'info' => 'const ' . $constant->name(),
            ];
        }

        return $symbolInformation;
    }
}
