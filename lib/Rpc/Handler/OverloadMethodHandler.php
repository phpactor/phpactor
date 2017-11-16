<?php

namespace Phpactor\Rpc\Handler;

use Phpactor\Rpc\Handler;
use Phpactor\Rpc\Response\OpenFileResponse;
use Phpactor\Core\GotoDefinition\GotoDefinition;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\Offset;
use InvalidArgumentException;
use Phpactor\Rpc\Response\Input\ChoiceInput;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\CodeTransform\Domain\Refactor\OverloadMethod;
use Phpactor\Rpc\Response\ReplaceFileSourceResponse;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Rpc\Response\Input\Input;

class OverloadMethodHandler extends AbstractHandler
{
    const NAME = 'overload_method';
    const PARAM_SOURCE = 'source';
    const PARAM_CLASS_NAME = self::CLASS_NAME;
    const PARAM_METHOD_NAME = 'method_name';
    const PARAM_PATH = 'path';
    const CLASS_NAME = 'class_name';

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var OverloadMethod
     */
    private $overloadMethod;

    public function __construct(Reflector $reflector, OverloadMethod $overloadMethod
    ) {
        $this->reflector = $reflector;
        $this->overloadMethod = $overloadMethod;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function defaultParameters(): array
    {
        return [
            self::PARAM_PATH => null,
            self::PARAM_SOURCE => null,
            self::PARAM_METHOD_NAME => null,
            self::CLASS_NAME => null,
        ];
    }

    public function handle(array $arguments)
    {
        if (null === $arguments[self::PARAM_SOURCE]) {
            throw new InvalidArgumentException(
                '"source" parameter is mandatory'
            );
        }

        if (null === $arguments[self::PARAM_PATH]) {
            throw new InvalidArgumentException(
                '"path" parameter is required'
            );
        }

        $classes = $this->reflector->reflectClassesIn($arguments[self::PARAM_SOURCE]);

        if ($classes->count() === 0) {
            throw new InvalidArgumentException(
                'No classes in source file'
            );
        }

        if (null === $arguments[self::PARAM_CLASS_NAME] && $classes->count() > 1) {
            throw new InvalidArgumentException(
                'Currently will only overload methods in files with one class'
            );
        }

        $class = $arguments[self::PARAM_CLASS_NAME] ? $classes->get($arguments[self::PARAM_CLASS_NAME]) : $classes->first();

        /** @var ReflectionClass $parentClass */
        $parentClass = $class->parent();

        if (null === $parentClass) {
            throw new InvalidArgumentException(sprintf(
                'Class "%s" has no parent', $class->name()
            ));
        }

        $methodNames = array_map(function (ReflectionMethod $method) {
            return $method->name();
        }, iterator_to_array(
            $parentClass->methods()->byVisibilities([ Visibility::public(), Visibility::protected() ])
        ));
        $methodNames = array_combine($methodNames, $methodNames);

        $this->requireArgument(self::PARAM_METHOD_NAME, ChoiceInput::fromNameLabelChoices(
            self::PARAM_METHOD_NAME,
            sprintf('Methods from "%s"', $parentClass->name()),
            $methodNames
        ));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $transformedCode = $this->overloadMethod->overloadMethod(
            SourceCode::fromString($arguments[self::PARAM_SOURCE]),
            (string) $class->name(),
            $arguments[self::PARAM_METHOD_NAME]
        );

        return ReplaceFileSourceResponse::fromPathAndSource($arguments[self::PARAM_PATH], (string) $transformedCode);
    }
}
