<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ParamTagPrototype;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockParamDiagnostic;
use Phpactor\WorseReflection\Reflector;

class UpdateDocblockParamsTransformer implements Transformer
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
        $docblocks = [];
        foreach ($diagnostics as $diagnostic) {
            $class = $this->reflector->reflectClassLike($diagnostic->classType());
            $method = $class->methods()->get($diagnostic->methodName());

            $classBuilder = $builder->classLike($method->class()->name()->short());
            $methodBuilder = $classBuilder->method($method->name());

            $methodBuilder->docblock(
                $this->docblockUpdater->set(
                    $methodBuilder->getDocblock() ? $methodBuilder->getDocblock()->__toString() : $method->docblock()->raw(),
                    new ParamTagPrototype(
                        $diagnostic->paramName(),
                        $diagnostic->paramType()
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

        $missings = $this->methodsThatNeedFixing($code);

        foreach ($missings as $missing) {
            $diagnostics[] = new Diagnostic(
                $missing->range(),
                sprintf(
                    'Missing @param %s',
                    $missing->paramName(),
                ),
                Diagnostic::WARNING
            );
        }

        /** @phpstan-ignore-next-line */
        return Diagnostics::fromArray($diagnostics);
    }

    /**
     * @return MissingDocblockParamDiagnostic[]
     */
    private function methodsThatNeedFixing(SourceCode $code): array
    {
        $missings = [];
        $diagnostics = $this->reflector->diagnostics($code->__toString())->byClass(MissingDocblockParamDiagnostic::class);

        foreach ($diagnostics as $diagnostic) {
            $missings[] = $diagnostic;
        }

        return $missings;
    }
}
