<?php

namespace Phpactor\Application;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\Offset;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Reflection\ReflectionMethod;
use Microsoft\PhpParser\Parser;

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
        foreach ($classReflection->methods() as $method) {
            $suggestions[] = [
                'type' => 'f',
                'name' => $method->name(),
            ];
        }

        if ($classReflection instanceof ReflectionClass) {
            foreach ($classReflection->properties() as $property) {
                $suggestions[] = [
                    'type' => 'm',
                    'name' => $property->name(),
                ];
            }
        }

        foreach ($classReflection->constants() as $constant) {
            $suggestions[] = [
                'type' => 'm',
                'name' => $constant->name(),
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

    private function getOffetToReflect($code, $offset)
    {
        $code = str_replace(PHP_EOL, ' ', $code);
        $untilCursor = substr($code, 0, $offset);

        foreach ([ '->', '::' ] as $accessor) {
            $pos = strrpos($untilCursor, $accessor);
            if ($pos) {
                return [ $pos,  substr($untilCursor, $pos + 2, $offset) ];
            }
        }

        return [ $offset, null ];
    }
}
