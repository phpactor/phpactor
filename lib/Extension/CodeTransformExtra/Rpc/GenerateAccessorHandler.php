<?php

namespace Phpactor\Extension\CodeTransformExtra\Rpc;

use InvalidArgumentException;
use Phpactor\CodeTransform\Domain\Refactor\GenerateAccessor;
use Phpactor\Extension\Rpc\Response\Input\ListInput;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\Rpc\Response\UpdateFileSourceResponse;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\Rpc\Handler\AbstractHandler;
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

    /**
     * @var Reflector
     */
    private $reflector;

    /**
     * @var GenerateAccessor
     */
    private $generateAccessor;

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

    public function configure(Resolver $resolver)
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
        $class = $this->class(
            $arguments[self::PARAM_SOURCE],
            $arguments[self::PARAM_OFFSET]
        );

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

        foreach ((array) $arguments[self::PARAM_NAMES] as $propertyName) {
            $newSource = $this->generateAccessor->generate(
                $newSource,
                $propertyName,
                $arguments[self::PARAM_OFFSET]
            );
        }

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $newSource->path(),
            $originalSource,
            (string) $newSource
        );
    }

    private function class(string $source, int $offset): ReflectionClass
    {
        $classes = $this->reflector->reflectClassesIn($source);

        if ($classes->count() === 1) {
            return $classes->first();
        }

        foreach ($classes as $class) {
            $position = $class->position();

            if ($position->start() <= $offset && $offset <= $position->end()) {
                return $class;
            }
        }

        throw new InvalidArgumentException(
            'No classes in source file'
        );
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
}
