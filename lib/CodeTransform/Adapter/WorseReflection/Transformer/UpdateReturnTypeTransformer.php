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
use Phpactor\WorseReflection\Core\Type\ClassType;
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
            $replacement = $method->frame()->returnType();
            $localReplacement = $replacement->toLocalType($method->scope())->generalize();
            $notNullReplacement = $replacement->stripNullable();

            if ($notNullReplacement instanceof ClassType) {
                $builder->use($notNullReplacement->name());
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
            if ($method->name() === '__construct') {
                continue;
            }
            $diagnostics[] = new Diagnostic(
                $method->nameRange(),
                sprintf(
                    'Missing return type `%s`',
                    $method->frame()->returnType()->toLocalType($method->scope())->toPhpString(),
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
