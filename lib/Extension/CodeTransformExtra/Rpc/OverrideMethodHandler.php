<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use Phpactor\MapResolver\Resolver;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Reflector;
use InvalidArgumentException;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\CodeTransform\Domain\Refactor\OverrideMethod;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;

class OverrideMethodHandler extends AbstractHandler
{
    const NAME = 'override_method';
    const PARAM_SOURCE = 'source';
    const PARAM_CLASS_NAME = 'class_name';
    const PARAM_METHOD_NAME = 'method_name';
    const PARAM_PATH = 'path';

    public function __construct(
        private Reflector $reflector,
        private OverrideMethod $overrideMethod
    ) {
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_METHOD_NAME => null,
            self::PARAM_CLASS_NAME => null,
        ]);
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
        ]);
    }

    public function handle(array $arguments)
    {
        $class = $this->class($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_CLASS_NAME]);
        $parentClass = $this->parentClass($class);

        $this->requireInput(ListInput::fromNameLabelChoices(
            self::PARAM_METHOD_NAME,
            sprintf('Methods from "%s"', $parentClass->name()),
            $this->methodChoices($parentClass)
        )->withMultiple(true));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $newCode = $arguments[self::PARAM_SOURCE];
        foreach ((array) $arguments[self::PARAM_METHOD_NAME] as $methodName) {
            $newCode = $this->overrideMethod->overrideMethod(
                SourceCode::fromString((string) $newCode),
                (string) $class->name(),
                $methodName
            )->apply($newCode);
        }

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments[self::PARAM_PATH],
            $arguments[self::PARAM_SOURCE],
            (string) $newCode
        );
    }

    private function class($source, $className = null)
    {
        $classes = $this->reflector->reflectClassesIn(TextDocumentBuilder::fromUnknown($source));

        if ($classes->count() === 0) {
            throw new InvalidArgumentException(
                'No classes in source file'
            );
        }

        if (null === $className && $classes->count() > 1) {
            throw new InvalidArgumentException(
                'Currently will only override methods in files with one class'
            );
        }

        return $className ? $classes->get($className) : $classes->first();
    }

    private function parentClass(ReflectionClass $class)
    {
        /** @var ReflectionClass $parentClass */
        $parentClass = $class->parent();

        if (null === $parentClass) {
            throw new InvalidArgumentException(sprintf(
                'Class "%s" has no parent',
                $class->name()
            ));
        }

        return $parentClass;
    }

    private function methodChoices(ReflectionClass $parentClass)
    {
        // TODO filter methods already implemented in the current class
        $methodNames = array_map(function (ReflectionMethod $method) {
            return $method->name();
        }, iterator_to_array(
            $parentClass->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ])
        ));

        sort($methodNames);

        return array_combine($methodNames, $methodNames);
    }
}
