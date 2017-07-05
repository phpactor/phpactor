<?php

namespace Phpactor\Application;

use DTL\ClassMover\ClassMover as ClassMoverFacade;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\Reflector\Domain\FilePath;
use Phpactor\Application\ClassCopy\MoveOperation;
use Phpactor\Phpactor;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use Phpactor\Application\Logger\ClassCopyLogger;
use DTL\Reflector\Domain\CopyReport;
use Phpactor\Application\Helper\ClassFileNormalizer;
use DTL\WorseReflection\ClassName;
use DTL\WorseReflection\Reflector;
use DTL\WorseReflection\Type;
use DTL\WorseReflection\Reflection\ReflectionClass;
use DTL\WorseReflection\Reflection\ReflectionMethod;

class ClassReflector
{
    private $classFileNormalizer;
    private $reflector;

    // rename compositetransformer => classToFileConverter
    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        Reflector $reflector
    ) {
        $this->classFileNormalizer = $classFileNormalizer;
        $this->reflector = $reflector;
    }

    /**
     * Move - guess if moving by class name or file.
     */
    public function reflect(string $classOrFile)
    {
        $className = $this->classFileNormalizer->normalizeToClass($classOrFile);
        $reflection = $this->reflector->reflectClass(ClassName::fromString($className));

        $return  = [
            'class' => (string) $reflection->name(),
            'class_namespace' => (string) $reflection->name()->namespace(),
            'class_name' => (string) $reflection->name()->short(),
            'methods' => [],
            'properties' => [],
        ];

        /** @var $method ReflectionMethod */
        foreach ($reflection->methods() as $method) {
            $methodInfo = [
                (string) $method->visibility() . ' function ' . $method->name()
            ];

            $return['methods'][$method->name()] = [
                'name' => $method->name(),
                'abstract' => $method->isAbstract(),
                'visibility' => (string) $method->visibility(),
                'parameters' => []
            ];

            $paramInfos = [];
            foreach ($method->parameters() as $parameter) {
                // build parameter synopsis
                $paramInfo = [];
                if ($parameter->hasType()) {
                    $paramInfo[] = $parameter->type()->className();
                }
                $paramInfo[] = '$' . $parameter->name();
                if ($parameter->hasDefault()) {
                    $paramInfo[] = ' = ' . var_export($parameter->default(), true);
                }
                $paramInfos[] = implode(' ', $paramInfo);

                $return['methods'][$method->name()]['parameters'][$parameter->name()] = [
                    'name' => $parameter->name(),
                    'has_type' => $parameter->hasType(),
                    'type' => $parameter->hasType() ? ($parameter->type()->className() ? $parameter->type()->className()->short(): (string) $parameter->type()) : null,
                    'has_default' => $parameter->hasDefault(),
                    'default' => $parameter->hasDefault() ? $parameter->default() : null,
                ];
            }

            $methodInfo[] = '(' . implode(', ', $paramInfos) . ')';

            if (Type::unknown() != $method->type()) {
                $methodInfo[] = ': ' . ($method->type()->className() ?: (string) $method->type());
            }

            $return['methods'][$method->name()]['type'] = $method->type()->className() ? $method->type()->className()->short(): (string) $method->type();

            $return['methods'][$method->name()]['synopsis'] = implode('', $methodInfo);
            $return['methods'][$method->name()]['docblock'] = $method->docblock()->formatted();
        }


        if ($reflection instanceof ReflectionClass) {
            foreach ($reflection->properties() as $property) {
                $return['properties'][$property->name()] = [
                    'name' => $property->name(),
                    'visibility' => (string) $property->visibility()
                ];
            }
        }

        return $return;
    }
}
