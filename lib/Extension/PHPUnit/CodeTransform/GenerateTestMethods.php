<?php

namespace Phpactor\Extension\PHPUnit\CodeTransform;

use Generator;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\WorseReflection\Reflector;
use Webmozart\Assert\Assert;

class GenerateTestMethods
{
    private const METHODS_TO_GENERATE = [
        'setUp',
        'tearDown',
    ];

    public function __construct(
        private Reflector $reflector,
        private Updater $updater,
    ) {
    }

    /** @return Generator<string> */
    public function getGeneratableTestMethods(SourceCode $source): Generator
    {
        $classes = $this->reflector->reflectClassLikesIn($source);
        if (count($classes->classes()) !== 1) {
            return;
        }

        $class = $classes->classes()->first();
        if (!$class instanceof ReflectionClass) {
            return;
        }

        if (!$class->isInstanceOf(ClassName::fromString('\PHPUnit\Framework\TestCase'))) {
            return;
        }

        foreach (self::METHODS_TO_GENERATE as $methodName) {
            if ($class->ownMembers()->methods()->byName($methodName)->count() === 0) {
                yield $methodName;
            }
        }

        return;
    }

    public function generateMethod(TextDocument $document, string $methodName): TextEdits
    {
        Assert::inArray(
            $methodName,
            self::METHODS_TO_GENERATE,
            sprintf('%s can not generate "%s" with class', __CLASS__, $methodName),
        );

        $class = $this->reflector->reflectClassLikesIn($document)->classes()->first();

        $builder = SourceCodeBuilder::create();
        $builder->namespace($class->name()->namespace());
        $classBuilder = $builder->class($class->name()->short());

        if ($class->methods()->has($methodName)) {
            return TextEdits::none();
        }

        $classBuilder ->method($methodName) ->visibility('public') ->returnType('void') ;

        return $this->updater->textEditsFor($builder->build(), Code::fromString($document->__toString()));
    }
}
