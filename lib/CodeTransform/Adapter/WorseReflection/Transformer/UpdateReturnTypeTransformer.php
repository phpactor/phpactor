<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\VoidType;
use Phpactor\WorseReflection\Reflector;

class UpdateReturnTypeTransformer implements Transformer
{
    private Reflector $reflector;

    private Updater $updater;

    private BuilderFactory $builderFactory;

    public function __construct(Reflector $reflector, Updater $updater, BuilderFactory $builderFactory)
    {
        $this->reflector = $reflector;
        $this->updater = $updater;
        $this->builderFactory = $builderFactory;
    }

    public function transform(SourceCode $code): TextEdits
    {
        $methods = $this->methodsThatNeedFixing($code);
        $builder = $this->builderFactory->fromSource($code);

        $class = null;
        foreach ($methods as $method) {
            $classBuilder = $builder->class($method->class()->name()->short());
            $methodBuilder = $classBuilder->method($method->name());
            $replacement = $this->returnType($method);
            $localReplacement = $replacement->toLocalType($method->scope())->generalize();
            $notNullReplacement = $replacement->stripNullable();

            foreach ($replacement->classNamedTypes() as $classType) {
                $builder->use($classType->name());
            }

            $methodBuilder->returnType($localReplacement->reduce()->toPhpString());
        }

        return $this->updater->textEditsFor($builder->build(), Code::fromString($code));
    }

    public function diagnostics(SourceCode $code): Diagnostics
    {
        $diagnostics = [];

        $methods = $this->methodsThatNeedFixing($code);

        foreach ($methods as $method) {
            $returnType = $this->returnType($method);
            $diagnostics[] = new Diagnostic(
                $method->nameRange(),
                sprintf(
                    'Missing return type `%s`',
                    $returnType->toLocalType($method->scope())->toPhpString(),
                ),
                Diagnostic::HINT
            );
        }

        /** @phpstan-ignore-next-line */
        return Diagnostics::fromArray($diagnostics);
    }

    /**
     * @return array<int,ReflectionMethod>
     */
    private function methodsThatNeedFixing(SourceCode $code): array
    {
        $methods = [];
        foreach ($this->reflector->reflectClassesIn($code->__toString()) as $class) {
            foreach ($class->methods()->belongingTo($class->name()) as $method) {
                if ($method->name() === '__construct') {
                    continue;
                }

                if ($method->type()->isDefined()) {
                    continue;
                }

                if ($method->docblock()->returnType()->isMixed()) {
                    continue;
                }

                $methods[] = $method;
            }
        }

        return $methods;
    }

    private function returnType(ReflectionMethod $method): Type
    {
        $returnType = $method->frame()->returnType();
        if (!$returnType->isDefined()) {
            return new VoidType();
        }
        return $returnType;
    }
}
