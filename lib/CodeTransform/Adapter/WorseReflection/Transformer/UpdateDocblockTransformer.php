<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Phpactor\CodeBuilder\Domain\BuilderFactory;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\DocblockParser\DocblockParser;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Reflector;

class UpdateDocblockTransformer implements Transformer
{
    private Reflector $reflector;

    private DocblockParser $parser;

    private Updater $updater;

    private BuilderFactory $builderFactory;

    public function __construct(Reflector $reflector, Updater $updater, BuilderFactory $builderFactory, DocblockParser $parser)
    {
        $this->reflector = $reflector;
        $this->parser = $parser;
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
            $replacement = $method->frame()->returnType()->toLocalType($method->scope())->generalize();

            if (!$method->docblock()->isDefined()) {
                $methodBuilder->docblock(
                    <<<EOT

                            /**
                             * @return {$replacement->__toString()}
                             */
                            
                        EOT
                );
                continue;
            }

            $node = $this->parser->parse($method->docblock()->raw());
            foreach ($node->descendantElements(ReturnTag::class) as $returnTag) {
                $methodBuilder->docblock(
                    TextEdits::fromTextEdits([
                        TextEdit::create(
                            $returnTag->start(),
                            $returnTag->end() - $returnTag->start(),
                            sprintf('@return %s', $method->frame()->returnType()->__toString())
                        )
                    ])->apply($node->toString())
                );
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

        $methods = $this->methodsThatNeedFixing($code);

        foreach ($methods as $method) {
            $diagnostics[] = new Diagnostic(
                ByteOffsetRange::fromInts($method->position()->start(), $method->position()->start() + strlen($method->name())),
                sprintf(
                    'Method "%s" returns `%s` but return type is `%s`',
                    $method->name(),
                    $method->frame()->returnType(),
                    $method->inferredType(),
                ),
                Diagnostic::WARNING
            );
        }

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
                $docblockType = $method->docblock()->returnType();
                $actualReturnType = $method->frame()->returnType()->generalize();
                $claimedReturnType = $method->inferredType();
                $phpReturnType = $method->type();

                // it's void
                if (false === $actualReturnType->isDefined()) {
                    continue;
                }

                if (
                    $claimedReturnType->isClass() && $actualReturnType->instanceof($claimedReturnType)->isTrue()
                ) {
                    if (!$actualReturnType instanceof GenericClassType) {
                        continue;
                    }
                }

                // docblock is exacvtly the same as native return type
                // fix it!
                if (
                    $docblockType->isDefined() &&
                    $docblockType->equals($phpReturnType)
                ) {
                    $methods[] = $method;
                    continue;
                }

                // docblock is defined and it is accepting the actual
                // it's OK
                if (
                    $docblockType->isDefined() &&
                    $docblockType->accepts($actualReturnType)->isTrue()
                ) {
                    continue;
                }
        
                // the docblock matches the generalized return type
                // it's OK
                if (
                    $claimedReturnType->equals($actualReturnType)
                ) {
                    continue;
                }
        
                // otherwise, fix it
                $methods[] = $method;
            }
        }
        return $methods;
    }
}
