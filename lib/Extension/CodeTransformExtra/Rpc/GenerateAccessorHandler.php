<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use InvalidArgumentException;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\Extension\Rpc\Response;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Phpactor\WorseReflection\Reflector;

class GenerateAccessorHandler extends AbstractHandler
{
    const NAME = 'generate_accessor';
    const PARAM_NAMES = 'names';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';
    const PARAM_OFFSET = 'offset';

    private Reflector $reflector;

    private GenerateAccessor $generateAccessor;

    public function __construct(
        Reflector $reflector,
        GenerateAccessor $generateAccessor
    ) {
        $this->reflector = $reflector;
        $this->generateAccessor = $generateAccessor;
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_NAMES => null,
        ]);
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
            self::PARAM_OFFSET,
        ]);
    }

    public function handle(array $arguments)
    {
        if ($context = $this->getPropertyContext($arguments)) {
            return $this->handleSingle($context, $arguments);
        }

        return $this->handleClass($arguments);
    }

    private function getPropertyContext(array $arguments): ?NodeContext
    {
        $offset = $this->reflector->reflectOffset($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_OFFSET]);

        if ($offset->symbolContext()->symbol()->symbolType() === Symbol::PROPERTY) {
            return $offset->symbolContext();
        }

        return null;
    }

    private function handleClass(array $arguments): Response
    {
        $class = $this->class($arguments[self::PARAM_SOURCE]);

        $this->requireInput(ListInput::fromNameLabelChoices(
            self::PARAM_NAMES,
            sprintf('Properties from "%s"', $class->name()),
            $this->propertiesChoices($class)
        )->withMultiple(true));

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $originalSource = $arguments[self::PARAM_SOURCE];
        $newSource = SourceCode::fromStringAndPath($originalSource, $arguments[self::PARAM_PATH]);

        $edits = $this->generateAccessor->generate(
            $newSource,
            (array)$arguments[self::PARAM_NAMES],
            $arguments[self::PARAM_OFFSET]
        );

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments[self::PARAM_PATH],
            $originalSource,
            $edits->apply($originalSource)
        );
    }

    /**
     * @param string $source
     *
     * @return ReflectionClass
     */
    private function class($source)
    {
        $classes = $this->reflector->reflectClassesIn($source);

        if ($classes->count() === 0) {
            throw new InvalidArgumentException(
                'No classes in source file'
            );
        }

        if ($classes->count() > 1) {
            throw new InvalidArgumentException(
                'Currently will only generates accessor by name in files with one class'
            );
        }

        return $classes->first();
    }

    private function propertiesChoices(ReflectionClass $class): array
    {
        // Select only those from the current class because the accessor generator
        // is not able to work with the parent class at the time
        $properties = $class->properties()->belongingTo($class->name());

        $propertiesNames = array_map(function (ReflectionProperty $property) {
            return $property->name();
        }, iterator_to_array($properties));

        natsort($propertiesNames);

        return array_combine($propertiesNames, $propertiesNames);
    }

    private function handleSingle(NodeContext $context, array $arguments)
    {
        $newSource = $this->generateAccessor->generate(
            SourceCode::fromStringAndPath($arguments[self::PARAM_SOURCE], $arguments[self::PARAM_PATH]),
            [$context->symbol()->name()],
            $arguments[self::PARAM_OFFSET]
        )->apply($arguments[self::PARAM_SOURCE]);

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $arguments[self::PARAM_PATH],
            $arguments[self::PARAM_SOURCE],
            (string) $newSource
        );
    }
}
