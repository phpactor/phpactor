<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Reflector;

class UpdateReturnTypeTransformer implements Transformer
{
    private Reflector $reflector;

    private Updater $updater;

    private BuilderFactory $builderFactory;

    private TextFormat $format;

    public function __construct(Reflector $reflector, Updater $updater, BuilderFactory $builderFactory, TextFormat $format)
    {
        $this->reflector = $reflector;
        $this->updater = $updater;
        $this->builderFactory = $builderFactory;
        $this->format = $format;
    }

    public function transform(SourceCode $code): TextEdits
    {
        $methods = $this->methodsThatNeedFixing($code);
        $builder = $this->builderFactory->fromSource($code);

        $class = null;
        foreach ($methods as $method) {
            $classBuilder = $builder->class($method->class()->name()->short());
            $methodBuilder = $classBuilder->method($method->name());
        }

        return $this->updater->textEditsFor($builder->build(), Code::fromString($code));
    }

    /**
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics(SourceCode $code): Diagnostics
    {
        $diagnostics = [];

        $methods = $this->methodsThatNeedFixing($code);

        foreach ($methods as $method) {
            $diagnostics[] = new Diagnostic(
                $method->nameRange(),
                sprintf(
                    'Missing @return %s',
                    $method->frame()->returnType()->toLocalType($method->scope())->generalize(),
                ),
                Diagnostic::WARNING
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
                if ($method->type()->isDefined()) {
                    continue;
                }
        
                $methods[] = $method;
            }
        }

        return $methods;
    }
}
