<?php

namespace Phpactor\Core\GotoDefinition;

use Phpactor\Core\GotoDefinition\Exception\GotoDefinitionException;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\Inference\Symbol;
use Phpactor\WorseReflection\Core\Reflection\Inference\SymbolInformation;
use Phpactor\WorseReflection\Reflector;

class GotoDefinition
{
    /**
     * @var Reflector
     */
    private $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }

    public function gotoDefinition(SymbolInformation $symbolInformation): GotoDefinitionResult
    {
        switch ($symbolInformation->symbol()->symbolType()) {
            case Symbol::METHOD:
            case Symbol::PROPERTY:
            case Symbol::CONSTANT:
                return $this->gotoMember($symbolInformation);
            case Symbol::CLASS_:
                return $this->gotoClass($symbolInformation);
        }

        throw new GotoDefinitionException(sprintf(
            'Do not know how to goto definition of symbol type "%s"',
            $symbolInformation->symbol()->symbolType()
        ));
    }

    public function gotoClass(SymbolInformation $symbolInformation): GotoDefinitionResult
    {
        $className = $symbolInformation->type();

        try {
            $class = $this->reflector->reflectClassLike(ClassName::fromString((string) $className));
        } catch (NotFound $e) {
            throw new GotoDefinitionException($e->getMessage());
        }

        $path = $class->sourceCode()->path();

        if (null === $path) {
            throw new GotoDefinitionResult(sprintf(
                'The source code for class "%s" has no path associated with it.',
                (string) $containingClass->name()
            ));
        }

        return GotoDefinitionResult::fromClassPathAndOffset(
            $path,
            $class->position()->start()
        );
    }

    private function gotoMember(SymbolInformation $symbolInformation)
    {
        $symbolName = $symbolInformation->symbol()->name();
        $symbolType = $symbolInformation->symbol()->symbolType();

        if (null === $symbolInformation->classType()) {
            throw new GotoDefinitionException(sprintf('Containing class for member "%s" could not be determined', $symbolName));
        }

        try {
            $containingClass = $this->reflector->reflectClassLike(ClassName::fromString((string) $symbolInformation->classType()));
        } catch (NotFound $e) {
            throw new GotoDefinitionException($e->getMessage());
        }

        if ($symbolType === Symbol::PROPERTY && $containingClass->isInterface()) {
            throw new GotoDefinitionException(sprintf('Symbol is a property and class "%s" is an interface', (string) $containingClass->name()));
        }

        $path = $containingClass->sourceCode()->path();

        if (null === $path) {
            throw new GotoDefinitionException(sprintf(
                'The source code for class "%s" has no path associated with it.',
                (string) $containingClass->name()
            ));
        }

        switch ($symbolType) {
            case Symbol::METHOD:
                $members = $containingClass->methods();
                break;
            case Symbol::CONSTANT:
                $members = $containingClass->constants();
                break;
            case Symbol::PROPERTY:
                $members = $containingClass->properties();
                break;
        }

        if (false === $members->has($symbolName)) {
            throw new GotoDefinitionException(sprintf(
                'Class "%s" has no %s named "%s", has: "%s"',
                $containingClass->name(),
                $symbolType,
                $symbolName,
                implode('", "', $members->keys())
            ));
        }

        $member = $members->get($symbolName);

        return GotoDefinitionResult::fromClassPathAndOffset(
            $path,
            $member->position()->start()
        );
    }
}
