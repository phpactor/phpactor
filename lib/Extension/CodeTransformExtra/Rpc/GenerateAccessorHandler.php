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
    const PARAM_OFFSET = 'offset';
    const PARAM_NAME = 'name';
    const PARAM_SOURCE = 'source';
    const PARAM_PATH = 'path';

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
            self::PARAM_OFFSET => null,
            self::PARAM_NAME => null,
        ]);
        $resolver->setRequired([
            self::PARAM_PATH,
            self::PARAM_SOURCE,
        ]);
    }

    public function handle(array $arguments)
    {
        $class = $this->class($arguments[self::PARAM_SOURCE]);

        if (!$arguments[self::PARAM_OFFSET]) {
            $this->requireInput(ListInput::fromNameLabelChoices(
                self::PARAM_NAME,
                sprintf('Properties from "%s"', $class->name()),
                $this->propertiesChoices($class)
            )->withMultiple(true));
        }

        if ($this->hasMissingArguments($arguments)) {
            return $this->createInputCallback($arguments);
        }

        $originalSource = $arguments[self::PARAM_SOURCE];
        $sourceCode = SourceCode::fromStringAndPath($originalSource, $arguments[self::PARAM_PATH]);

        foreach ($this->propertiesOffsets($arguments, $class) as $offset) {
            $sourceCode = $this->generateAccessor->generateAccessor($sourceCode, $offset);
        }

        return UpdateFileSourceResponse::fromPathOldAndNewSource(
            $sourceCode->path(),
            $originalSource,
            (string) $sourceCode
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

    private function propertiesOffsets(array $arguments, ReflectionClass $class): array
    {
        $offset = $arguments[self::PARAM_OFFSET];

        if (is_int($offset) || ctype_digit($offset)) {
            return [$offset];
        }

        $offsets = [];
        foreach ((array) $arguments[self::PARAM_NAME] as $propertyName) {
            $offsets[] = $this->propertyOffset($class, $propertyName);
        }

        return $offsets;
    }

    private function propertyOffset(ReflectionClass $class, string $propertyName): int
    {
        $property = $class->properties()->get($propertyName);

        $start = $property->position()->start();
        $visibility = $property->visibility();

        // start is right before the visibility
        // and the offset start at the first space after the visibility
        $offset = $start + strlen($visibility) + 1;

        return $offset;
    }

    private function propertiesChoices(ReflectionClass $class): array
    {
        // Select only those from the current class because the accessor generator
        // is not able to work with the parent class at the time
        $properties = $class->properties()->belongingTo($class->name());

        $propertiesNames = array_map(function (ReflectionProperty $property) {
            return $property->name();
        }, iterator_to_array($properties));

        sort($propertiesNames);

        return array_combine($propertiesNames, $propertiesNames);
    }
}
