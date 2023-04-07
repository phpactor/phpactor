<?php

namespace Phpactor\Extension\PHPUnit\CodeTransform\Domain\Refactor;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentEdits;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\TextDocument\WorkspaceEdits;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Reflector;


class GenerateTestMethods /* implements GenerateMethod */
{
    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
    ) {
    }

    /** @return Generator<string> */
    public function getGeneratableTestMethods(SourceCode $source): Generator
    {
        $classes = $this->reflector->reflectClassesIn($source);
        if (count($classes->classes()) !== 1) {
            return;
        }

        $class = $classes->classes()->first();
        if (!$class instanceof ReflectionClass) {
            return;
        }

        if (!$class->isInstanceOf(ClassName::fromString(TestCase::class))) {
            dump('Not a unit test');
            return;
        }

        yield 'setUp';
    }

    public function generateMethod(TextDocument $document, ByteOffset $offset): WorkspaceEdits
    {
        $class = $this->reflector->reflectClassesIn($document)->classes()->first();

        $builder = SourceCodeBuilder::create();
        $builder->namespace($class->name()->namespace());
        $classBuilder = $builder->class($class->name()->short());

        foreach(['setUp', 'tearDown'] as $methodName) {
            try {
                if ($class->methods()->has($methodName)) {
                    continue;
                }
            } catch (NotFound) {
                continue;
            }

            $setUpMethod = $classBuilder->method($methodName);
            $setUpMethod->visibility('public');
        }

        $sourceCode = SourceCode::fromUnknown((string) $document->__toString());
        return new WorkspaceEdits(
            new TextDocumentEdits(
                TextDocumentUri::fromString($sourceCode->mustGetUri()),
                $this->updater->textEditsFor($builder->build(), Code::fromString($document->__toString()))
            )
        );
    }
}
