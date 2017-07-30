<?php

namespace Phpactor\Application;

use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCode;
use Phpactor\WorseReflection\Offset;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflection\ReflectionClass;

class Autocomplete
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

    public function autocomplete(string $code, int $offset)
    {
        $debug = fopen('debug.txt', 'a+');
        $code = $this->filesystemHelper->contentsFromFileOrStdin($code);
        $reflectionOffset = $this->reflector->reflectOffset(
            SourceCode::fromString($code),
            Offset::fromint($offset)
        );

        $type = $reflectionOffset->value()->type();
        fwrite($debug, $offset . (string) $type . PHP_EOL);

        if ($type->isPrimitive()) {
            return [];
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

        return $suggestions;
    }
}
