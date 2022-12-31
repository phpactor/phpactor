<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ReturnTagPrototype;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockReturnTypeDiagnostic;
use Phpactor\WorseReflection\Reflector;

class UpdateDocblockReturnTransformer implements Transformer
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private BuilderFactory $builderFactory,
        private DocBlockUpdater $docblockUpdater,
    ) {
    }

    public function transform(SourceCode $code): TextEdits
    {
        $diagnostics = $this->methodsThatNeedFixing($code);
        $builder = $this->builderFactory->fromSource($code);

        $class = null;
        foreach ($diagnostics as $diagnostic) {
            $class = $this->reflector->reflectClassLike($diagnostic->classType());
            $method = $class->methods()->get($diagnostic->methodName());

            $classBuilder = $builder->classLike($method->class()->name()->short());
            $methodBuilder = $classBuilder->method($method->name());
            $replacement = $method->frame()->returnType();
            $localReplacement = $replacement->toLocalType($method->scope())->generalize();

            foreach ($replacement->classLikeTypes() as $classType) {
                $builder->use($classType->toPhpString());
            }

            $methodBuilder->docblock(
                $this->docblockUpdater->set(
                    $methodBuilder->getDocblock() ? $methodBuilder->getDocblock()->__toString() : $method->docblock()->raw(),
                    new ReturnTagPrototype(
                        $localReplacement
                    )
                )
            );
        }

        return $this->updater->textEditsFor($builder->build(), Code::fromString($code));
    }

    /**
     * @return Diagnostics<Diagnostic>
     */
    public function diagnostics(SourceCode $code): Diagnostics
    {
        $diagnostics = [];

        $missingDocblocks = $this->methodsThatNeedFixing($code);

        foreach ($missingDocblocks as $missingDocblock) {
            $diagnostics[] = new Diagnostic(
                $missingDocblock->range(),
                sprintf(
                    'Missing @return %s',
                    $missingDocblock->actualReturnType(),
                ),
                Diagnostic::WARNING
            );
        }

        /** @phpstan-ignore-next-line */
        return Diagnostics::fromArray($diagnostics);
    }

    /**
     * @return MissingDocblockReturnTypeDiagnostic[]
     */
    private function methodsThatNeedFixing(SourceCode $code): array
    {
        $missingMethods = [];
        $diagnostics = $this->reflector->diagnostics($code->__toString())->byClasses(MissingDocblockReturnTypeDiagnostic::class);

        foreach ($diagnostics as $diagnostic) {
            $missingMethods[] = $diagnostic;
        }

        return $missingMethods;
    }
}
