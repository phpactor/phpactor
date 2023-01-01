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
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingReturnTypeDiagnostic;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Reflector;

class UpdateReturnTypeTransformer implements Transformer
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private BuilderFactory $builderFactory
    ) {
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

            foreach ($replacement->toTypes()->classLike() as $classType) {
                $builder->use($classType->name());
            }

            $methodBuilder->returnType($localReplacement->reduce()->toPhpString(), $localReplacement->reduce());
        }

        return $this->updater->textEditsFor($builder->build(), Code::fromString($code));
    }

    public function diagnostics(SourceCode $code): Diagnostics
    {
        $wrDiagnostics = $this->reflector->diagnostics($code->__toString())->byClass(MissingReturnTypeDiagnostic::class);
        $diagnostics = [];

        /** @var MissingReturnTypeDiagnostic $diagnostic */
        foreach ($wrDiagnostics as $diagnostic) {
            if (!$diagnostic->returnType()->isDefined()) {
                continue;
            }

            $diagnostics[] = new Diagnostic(
                $diagnostic->range(),
                $diagnostic->message(),
                Diagnostic::WARNING,
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
        $diagnostics = $this->reflector->diagnostics($code->__toString())->byClass(MissingReturnTypeDiagnostic::class);
        $methods = [];
        /** @var MissingReturnTypeDiagnostic $diagnostic */
        foreach ($diagnostics as $diagnostic) {
            if (!$diagnostic->returnType()->isDefined()) {
                continue;
            }

            $class = $this->reflector->reflectClassLike($diagnostic->classType());
            $methods[] = $class->methods()->get($diagnostic->methodName());
        }

        return $methods;
    }

    private function returnType(ReflectionMethod $method): Type
    {
        $returnType = $method->frame()->returnType();
        return $returnType;
    }
}
