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
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockDiagnostic;
use Phpactor\WorseReflection\Reflector;

class UpdateDocblockTransformer implements Transformer
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
        private BuilderFactory $builderFactory,
        private TextFormat $format
    ) {
    }

    public function transform(SourceCode $code): TextEdits
    {
        $missingMethods = $this->methodsThatNeedFixing($code);
        $builder = $this->builderFactory->fromSource($code);

        $class = null;
        foreach ($missingMethods as $method) {
            $class = $this->reflector->reflectClassLike($method->classType());
            $method = $class->methods()->get($method->methodName());

            $classBuilder = $builder->classLike($method->class()->name()->short());
            $methodBuilder = $classBuilder->method($method->name());
            $replacement = $method->frame()->returnType();
            $localReplacement = $replacement->toLocalType($method->scope())->generalize();

            foreach ($replacement->classLikeTypes() as $classType) {
                $builder->use($classType->toPhpString());
            }

            if (!$method->docblock()->isDefined()) {
                $methodBuilder->docblock("\n\n".$this->format->indent(
                    <<<EOT
                        /**
                         * @return {$localReplacement->__toString()}
                         */
                        EOT
                    ,
                    1
                ). "\n".$this->format->indent('', 1));
                continue;
            }
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
     * @return array<int,MissingDocblockDiagnostic>
     */
    private function methodsThatNeedFixing(SourceCode $code): array
    {
        $missingMethods = [];
        $diagnostics = $this->reflector->diagnostics($code->__toString())->byClass(MissingDocblockDiagnostic::class);

        /** @var MissingDocblockDiagnostic $diagnostic */
        foreach ($diagnostics as $diagnostic) {
            $missingMethods[] = $diagnostic;
        }

        return $missingMethods;
    }
}
