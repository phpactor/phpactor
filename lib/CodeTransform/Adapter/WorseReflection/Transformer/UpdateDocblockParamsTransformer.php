<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Amp\Promise;
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
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingParamDiagnostic;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class UpdateDocblockParamsTransformer implements Transformer
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private BuilderFactory $builderFactory,
        private DocBlockUpdater $docblockUpdater,
    ) {
    }

    /**
        * @return Promise<TextEdits>
     */
    public function transform(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $diagnostics = yield $this->methodsThatNeedFixing($code);
            $builder = $this->builderFactory->fromSource($code);

            $class = null;
            $docblocks = [];
            foreach ($diagnostics as $diagnostic) {
                $class = $this->reflector->reflectClassLike($diagnostic->classType());
                $method = $class->methods()->get($diagnostic->methodName());

                $classBuilder = $builder->classLike($method->class()->name()->short());
                $methodBuilder = $classBuilder->method($method->name());

                foreach ($diagnostic->paramType()->allTypes()->classLike() as $classType) {
                    $builder->use($classType->name()->__toString());
                }

                $methodBuilder->docblock(
                    $this->docblockUpdater->set(
                        $methodBuilder->getDocblock() ? $methodBuilder->getDocblock()->__toString() : $method->docblock()->raw(),
                        new ParamTagPrototype(
                            $diagnostic->paramName(),
                            $diagnostic->paramType()->toLocalType($method->scope())
                        )
                    )
                );
            }

            return $this->updater->textEditsFor($builder->build(), Code::fromString($code));
        });
    }

    /**
     * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $diagnostics = [];

            $missings = yield $this->methodsThatNeedFixing($code);

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

            return Diagnostics::fromArray($diagnostics);
        });
    }

    /**
     * @return Promise<DocblockMissingParamDiagnostic[]>
     */
    private function methodsThatNeedFixing(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            $missings = [];
            $diagnostics = (yield $this->reflector->diagnostics($code))->byClass(DocblockMissingParamDiagnostic::class);

            foreach ($diagnostics as $diagnostic) {
                $missings[] = $diagnostic;
            }

            return $missings;
        });
    }
}
