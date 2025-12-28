<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Amp\Promise;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
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
use function Amp\call;

class UpdateReturnTypeTransformer implements Transformer
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private BuilderFactory $builderFactory
    ) {
    }

    /**
        * @return Promise<TextEdits>
     */
    public function transform(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $methods = yield $this->methodsThatNeedFixing($code);
            $builder = $this->builderFactory->fromSource($code);

            $class = null;
            foreach ($methods as $method) {
                $classBuilder = $builder->class($method->class()->name()->short());
                $methodBuilder = $classBuilder->method($method->name());
                $replacement = $this->returnType($method);
                $localReplacement = $replacement->toLocalType($method->scope());
                $notNullReplacement = $replacement->stripNullable();

                foreach ($replacement->allTypes()->classLike() as $classType) {
                    $builder->use($classType->name());
                }

                $methodBuilder->returnType($localReplacement->reduce()->toPhpString(), $localReplacement->reduce());
            }

            return $this->updater->textEditsFor($builder->build(), $code);
        });
    }

    /**
        * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $wrDiagnostics = (yield $this->reflector->diagnostics($code))->byClass(MissingReturnTypeDiagnostic::class);
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
        });
    }

    /**
     * @return Promise<array<int,ReflectionMethod>>
     */
    private function methodsThatNeedFixing(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $diagnostics = (yield $this->reflector->diagnostics($code))->byClass(MissingReturnTypeDiagnostic::class);
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
        });
    }

    private function returnType(ReflectionMethod $method): Type
    {
        $returnType = $method->frame()->returnType();
        return $returnType;
    }
}
