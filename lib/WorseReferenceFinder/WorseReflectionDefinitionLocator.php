<?php

namespace Phpactor\WorseReferenceFinder;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextDocumentUri;
use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\ClassHierarchyResolver;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Context\MemberDeclarationContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionEnum;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionDefinitionLocator implements DefinitionLocator
{
    public function __construct(private Reflector $reflector, private Cache $cache)
    {
    }


    public function locateDefinition(TextDocument $document, ByteOffset $byteOffset): TypeLocations
    {
        if (false === $document->language()->isPhp()) {
            throw new CouldNotLocateDefinition('I only work with PHP files');
        }

        $this->cache->purge();

        if ($uri = $document->uri()) {
            $sourceCode = SourceCode::fromPathAndString($uri->__toString(), $document->__toString());
        } else {
            $sourceCode = SourceCode::fromString($document->__toString());
        }

        try {
            $offset = $this->reflector->reflectOffset(
                $sourceCode,
                $byteOffset->toInt()
            );
        } catch (NotFound $notFound) {
            throw new CouldNotLocateDefinition($notFound->getMessage(), 0, $notFound);
        }

        $typeLocations = [];
        $typeLocations = $this->gotoDefinition($document, $offset);

        if ($typeLocations->count() === 0) {
            throw new CouldNotLocateDefinition('No definition(s) found');
        }

        return $typeLocations;
    }

    private function gotoDefinition(TextDocument $document, ReflectionOffset $offset): TypeLocations
    {
        $symbolContext = $offset->nodeContext();

        if ($symbolContext instanceof MemberDeclarationContext) {
            return $this->gotoMethodDeclaration($symbolContext);
        }
        return match ($symbolContext->symbol()->symbolType()) {
            Symbol::METHOD, Symbol::PROPERTY, Symbol::CONSTANT, Symbol::CASE => $this->gotoMember($symbolContext),
            Symbol::CLASS_ => $this->gotoClass($symbolContext),
            Symbol::FUNCTION => $this->gotoFunction($symbolContext),
            Symbol::DECLARED_CONSTANT => $this->gotoDeclaredConstant($symbolContext),
            default => throw new CouldNotLocateDefinition(sprintf(
                'Do not know how to goto definition of symbol type "%s"',
                $symbolContext->symbol()->symbolType()
            )),
        };
    }

    private function gotoClass(NodeContext $symbolContext): TypeLocations
    {
        $className = $symbolContext->type();

        if (!$className instanceof ClassType) {
            throw new CouldNotLocateDefinition(sprintf(
                'member container type is not a class type, it is a "%s"',
                get_class($className)
            ));
        }

        try {
            $class = $this->reflector->reflectClassLike(
                $className->name()
            );
        } catch (NotFound $e) {
            throw new CouldNotLocateDefinition($e->getMessage(), 0, $e);
        }

        $path = $class->sourceCode()->path();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for class "%s" has no path associated with it.',
                $class->name()
            ));
        }

        return new TypeLocations([new TypeLocation($className, new Location(
            TextDocumentUri::fromString($path),
            ByteOffset::fromInt($class->position()->start())
        ))]);
    }

    private function gotoFunction(NodeContext $symbolContext): TypeLocations
    {
        $functionName = $symbolContext->symbol()->name();

        try {
            $function = $this->reflector->reflectFunction($functionName);
        } catch (NotFound $e) {
            throw new CouldNotLocateDefinition($e->getMessage(), 0, $e);
        }

        $path = $function->sourceCode()->path();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for function "%s" has no path associated with it.',
                $function->name()
            ));
        }

        return new TypeLocations([
            new TypeLocation(TypeFactory::unknown(), new Location(
                TextDocumentUri::fromString($path),
                ByteOffset::fromInt($function->position()->start())
            ))
        ]);
    }

    private function gotoDeclaredConstant(NodeContext $symbolContext): TypeLocations
    {
        $constantName = $symbolContext->symbol()->name();

        try {
            $constant = $this->reflector->reflectConstant($constantName);
        } catch (NotFound $e) {
            throw new CouldNotLocateDefinition($e->getMessage(), 0, $e);
        }

        $path = $constant->sourceCode()->path();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for constant "%s" has no path associated with it.',
                $constant->name()
            ));
        }

        return new TypeLocations([
            new TypeLocation(TypeFactory::unknown(), new Location(
                TextDocumentUri::fromString($path),
                ByteOffset::fromInt($constant->position()->start())
            ))
        ]);
    }

    private function gotoMember(NodeContext $symbolContext): TypeLocations
    {
        $symbolName = $symbolContext->symbol()->name();
        $symbolType = $symbolContext->symbol()->symbolType();
        $containerType = $symbolContext->containerType();

        $locations = [];
        foreach ($containerType->expandTypes()->classLike() as $namedType) {
            try {
                $containingClass = $this->reflector->reflectClassLike($namedType->name());
            } catch (NotFound) {
                continue;
            }

            if ($symbolType === Symbol::PROPERTY && $containingClass instanceof ReflectionInterface) {
                throw new CouldNotLocateDefinition(sprintf('Symbol is a property and class "%s" is an interface', (string) $containingClass->name()));
            }

            switch ($symbolType) {
                case Symbol::METHOD:
                    $members = $containingClass->methods();
                    break;
                case Symbol::CONSTANT:
                    if ($containingClass instanceof ReflectionEnum) {
                        $members = $containingClass->cases();
                        break;
                    }
                    assert($containingClass instanceof ReflectionClass || $containingClass instanceof ReflectionInterface);
                    $members = $containingClass->constants();
                    break;
                case Symbol::PROPERTY:
                    assert($containingClass instanceof ReflectionClass || $containingClass instanceof ReflectionTrait || $containingClass instanceof ReflectionEnum);
                    $members = $containingClass->properties();
                    break;
                default:
                    throw new CouldNotLocateDefinition(sprintf(
                        'Unhandled symbol type "%s"',
                        $symbolType
                    ));
            }

            if (false === $members->has($symbolName)) {
                continue;
            }

            $member = $members->get($symbolName);

            $path = $member->declaringClass()->sourceCode()->path();

            if (null === $path) {
                throw new CouldNotLocateDefinition(sprintf(
                    'The source code for class "%s" has no path associated with it.',
                    (string) $containingClass->name()
                ));
            }

            $locations[] = new TypeLocation($namedType, new Location(
                TextDocumentUri::fromString($path),
                ByteOffset::fromInt($member->position()->start())
            ));
        }

        return new TypeLocations($locations);
    }

    private function gotoMethodDeclaration(MemberDeclarationContext $symbolContext): TypeLocations
    {
        try {
            $class = $this->reflector->reflectClass($symbolContext->classType()->name());
        } catch (NotFound) {
            return new TypeLocations([]);
        }

        // find first parent definition or return declaring class
        $member = (function (string $name) use ($class) {
            foreach ((new ClassHierarchyResolver())->resolve($class) as $currentClass) {
                if ($currentClass->ownMembers()->has($name)) {
                    return $currentClass->ownMembers()->byName($name)->first();
                }
            }
            return null;
        })($symbolContext->name());

        if (null === $member) {
            return new TypeLocations([]);
        }


        $path = $member->declaringClass()->sourceCode()->path();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for class "%s" has no path associated with it.',
                (string) $member->declaringClass()->name()
            ));
        }

        return new TypeLocations([new TypeLocation($symbolContext->classType(), new Location(
            TextDocumentUri::fromString($path),
            ByteOffset::fromInt($member->position()->start())
        ))]);
    }
}
