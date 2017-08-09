<?php

namespace Phpactor\Application;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\Offset;
use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;

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

    public function complete(string $code, int $offset): array
    {
        $code = $this->filesystemHelper->contentsFromFileOrStdin($code);
        list($offset, $partialMatch) = $this->getOffetToReflect($code, $offset);

        $reflectionOffset = $this->reflector->reflectOffset(
            SourceCode::fromString($code),
            Offset::fromint($offset)
        );

        $type = $reflectionOffset->value()->type();
        $response = [
            'suggestions' => []
        ];

        if ($type->isPrimitive()) {
            return $response;
        }

        $classReflection = $this->reflector->reflectClass(ClassName::fromString((string) $type));

        $suggestions = [];
        /** @var $method ReflectionMethod */
        foreach ($classReflection->methods() as $method) {
            if ($method->name() === '__construct') {
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

        // filter partial match
        if ($partialMatch) {
            $suggestions = array_filter($suggestions, function ($suggestion) use ($partialMatch) {
                return 0 === strpos($suggestion['name'], $partialMatch);
            });
        }

        return ['suggestions' => array_values($suggestions) ];
    }

    /**
     * Hello
     */
    private function getOffetToReflect($code, $offset)
    {
        $code = str_replace(PHP_EOL, ' ', $code);
        $untilCursor = substr($code, 0, $offset);

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

        $returnType = $method->inferredReturnType();
        if ($returnType->isDefined()) {
            $info[] = ': ' . (string) $returnType->short();
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

        if ($property->type()->isDefined()) {
            $info[] = ': ' . (string) $property->type()->short();
        }

        return implode('', $info);
    }
}
