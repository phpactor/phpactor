<?php

namespace Phpactor\Application;

use Phpactor\ClassMover\ClassMover as ClassMoverFacade;
use Phpactor\ClassMover\Domain\FullyQualifiedName;
use Phpactor\Reflector\Domain\FilePath;
use Phpactor\Application\ClassCopy\MoveOperation;
use Phpactor\Phpactor;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use Phpactor\Application\Logger\ClassCopyLogger;
use Phpactor\Reflector\Domain\CopyReport;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\WorseReflection\ClassName;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Type;
use Phpactor\WorseReflection\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Reflection\ReflectionConstant;

class ClassReflector
{
    const FOOBAR = 'foo';

    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    /**
     * @var Reflector
     */
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
            'constants' => [],
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
                'parameters' => [],
                'static' => $method->isStatic() ? 1 : 0
            ];

            $paramInfos = [];
            foreach ($method->parameters() as $parameter) {
                $parameterType = $parameter->type();
                // build parameter synopsis
                $paramInfo = [];
                if ($parameter->hasType()) {
                    $paramInfo[] = $parameterType->className() ?: (string) $parameterType;
                }
                $paramInfo[] = '$' . $parameter->name();
                if ($parameter->hasDefault()) {
                    $paramInfo[] = ' = ' . var_export($parameter->default(), true);
                }
                $paramInfos[] = implode(' ', $paramInfo);

                $return['methods'][$method->name()]['parameters'][$parameter->name()] = [
                    'name' => $parameter->name(),
                    'has_type' => $parameter->hasType(),
                    'type' => $parameter->hasType() ? ($parameterType->className() ? $parameterType->className()->short(): (string) $parameterType) : null,
                    'has_default' => $parameter->hasDefault(),
                    'default' => $parameter->hasDefault() ? $parameter->default() : null,
                ];
            }

            $methodInfo[] = '(' . implode(', ', $paramInfos) . ')';
            $methodType = $method->type();


            if (Type::unknown() != $methodType) {
                $methodInfo[] = ': ' . ($methodType->isPrimitive() ? (string) $methodType : $methodType->className()->short());
            }

            $return['methods'][$method->name()]['type'] = $methodType->isPrimitive() ? (string) $methodType : $methodType->className()->short();

            $return['methods'][$method->name()]['synopsis'] = implode('', $methodInfo);
            $return['methods'][$method->name()]['docblock'] = $method->docblock()->formatted();
        }

        /** @var $constant ReflectionConstant */
        foreach ($reflection->constants() as $constant) {
            $return['constants'][$constant->name()] = [
                'name' => $constant->name()
            ];
        }


        if (!$reflection instanceof ReflectionClass) {
            return $return;
        }

        /** @var $property ReflectionProperty */
        foreach ($reflection->properties() as $property) {
            $return['properties'][$property->name()] = [
                'name' => $property->name(),
                'visibility' => (string) $property->visibility(),
                'static' => $property->isStatic() ? 1 : 0,
                'info' => sprintf(
                    '%s %s $%s',
                    (string) $property->visibility(),
                    $property->type()->isPrimitive() ? (string) $property->type() : (string) $property->type()->className(),
                    $property->name()
                ),
            ];
        }

        return $return;
    }
}
