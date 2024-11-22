<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Amp\Promise;
use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\DocBlockUpdater;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ExtendsTagPrototype;
use Phpactor\CodeTransform\Domain\DocBlockUpdater\ImplementsTagPrototype;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DocblockMissingClassGenericDiagnostic;
use Phpactor\WorseReflection\Reflector;
use function Amp\call;

class UpdateDocblockGenericTransformer implements Transformer
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
            $diagnostics = yield $this->wrDiagnostics($code);
            $builder = $this->builderFactory->fromSource($code);

            $class = null;
            $docblocks = [];
            foreach ($diagnostics as $diagnostic) {
                /** @var DocblockMissingClassGenericDiagnostic $diagnostic */
                $class = $this->reflector->reflectClassLike($diagnostic->className());

                $classBuilder = $builder->classLike($class->name()->short());

                foreach ($diagnostic->missingGenericType()->allTypes()->classLike() as $classType) {
                    $builder->use($classType->name()->__toString());
                }

                $tag = match($diagnostic->isExtends()) {
                    true => new ExtendsTagPrototype(
                        $diagnostic->missingGenericType(),
                    ),
                    false => new ImplementsTagPrototype(
                        $diagnostic->missingGenericType(),
                    ),
                };
                $classBuilder->docblock(
                    $this->docblockUpdater->set(
                        $classBuilder->getDocblock() ? $classBuilder->getDocblock()->__toString() : $class->docblock()->raw(),
                        $tag
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

            $missings = yield $this->wrDiagnostics($code);

            foreach ($missings as $missing) {
                $diagnostics[] = new Diagnostic(
                    $missing->range(),
                    $missing->message(),
                    Diagnostic::WARNING
                );
            }

            return Diagnostics::fromArray($diagnostics);
        });
    }

    /**
     * @return Promise<DocblockMissingClassGenericDiagnostic[]>
     */
    private function wrDiagnostics(SourceCode $code): Promise
    {
        return call(function () use ($code) {
            return (yield $this->reflector->diagnostics($code))->byClass(DocblockMissingClassGenericDiagnostic::class);
        });
    }
}
