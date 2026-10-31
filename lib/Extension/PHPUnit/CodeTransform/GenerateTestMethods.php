<?php

namespace Phpactor\Extension\PHPUnit\CodeTransform;

use Generator;
use Phpactor\CodeTransform\Adapter\WorseReflection\Refactor\WorseOverrideMethod;
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
        private WorseOverrideMethod $overrideMethod,
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
        $source = SourceCode::fromTextDocument($document);

        Assert::inArray(
            $methodName,
            self::METHODS_TO_GENERATE,
            sprintf('%s can not generate "%s" with class', __CLASS__, $methodName),
        );

        $class = $this->reflector->reflectClassesIn($document)->classes()->first();

        return $this->overrideMethod->overrideMethod($source, (string) $class->name(), $methodName);
    }
}
