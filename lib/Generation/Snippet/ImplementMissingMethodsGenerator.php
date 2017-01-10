<?php

namespace Phpactor\Generation\Snippet;

use BetterReflection\Reflector\ClassReflector;
use Phpactor\CodeContext;
use Phpactor\Util\ClassUtil;
use BetterReflection\Reflection\ReflectionMethod;
use Phpactor\Generation\SnippetGeneratorInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImplementMissingMethodsGenerator implements SnippetGeneratorInterface
{
    /**
     * @var ClassReflector
     */
    private $classReflector;

    /**
     * @var ClassUtil
     */
    private $classUtil;

    public function __construct(ClassReflector $classReflector, ClassUtil $classUtil)
    {
        $this->classReflector = $classReflector;
        $this->classUtil = $classUtil;
    }

    public function generate(CodeContext $codeContext, array $options): string
    {
        $missingMethods = $this->resolveMissingMethods($codeContext);

        $snippet = [];

        foreach ($missingMethods as $missingMethod) {
            $snippet[] = '/**';
            $snippet[] = ' * {@inheritDoc}';
            $snippet[] = ' */';
            $snippet[] = sprintf(
                '%s function %s(%s)',
                $missingMethod->isProtected() ? 'protected' : 'public',
                $missingMethod->getName(),
                $this->getMethodArgs($missingMethod)
            );
            $snippet[] = '{';
            $snippet[] = '}';
            $snippet[] = PHP_EOL;
        }

        return implode(PHP_EOL, $snippet);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    private function resolveMissingMethods(CodeContext $codeContext)
    {
        $reflection = $this->classReflector->reflect(
            $this->classUtil->getClassNameFromSource($codeContext->getSource())
        );

        $missingMethods = array_filter($reflection->getMethods(), function ($method) {
            return $method->isAbstract() || $method->getDeclaringClass()->isInterface();
        });

        return $missingMethods;
    }

    private function getMethodArgs(ReflectionMethod $reflectionMethod)
    {
        $args = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $argString = [];
            $argString[] = (string) $parameter->getTypeHint() ? (string) $parameter->getTypeHint() . ' ' : '';
            $argString[] = '$' . $parameter->getName();

            if ($parameter->isDefaultValueAvailable()) {
                $default = $parameter->getDefaultValueAsString();
                $argString[] = ' = ' . $default;
            }
            $args[] = implode('', $argString);
        }

        return implode(', ', $args);
    }
}
