<?php

namespace Phpactor\WorseReferenceFinder;

use Phpactor\ReferenceFinder\DefinitionLocator;
use Phpactor\ReferenceFinder\Exception\CouldNotLocateDefinition;
use Phpactor\ReferenceFinder\TypeLocation;
use Phpactor\ReferenceFinder\TypeLocations;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\Location;
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
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Reflector;

class WorseReflectionDefinitionLocator implements DefinitionLocator
{
    public function __construct(private Reflector $reflector, private Cache $cache)
    {
    }


    public function locateDefinition(TextDocument $textDocument, ByteOffset $byteOffset): TypeLocations
    {
        if (false === $textDocument->language()->isPhp()) {
            throw new CouldNotLocateDefinition('I only work with PHP files');
        }

        $this->cache->purge();

        try {
            $offset = $this->reflector->reflectOffset(
                $textDocument,
                $byteOffset->toInt()
            );
        } catch (NotFound $notFound) {
            throw new CouldNotLocateDefinition($notFound->getMessage(), 0, $notFound);
        }

        $typeLocations = [];
        $typeLocations = $this->gotoDefinition($textDocument, $offset);

        if ($typeLocations->count() === 0) {
            throw new CouldNotLocateDefinition('No definition(s) found');
        }

        return $typeLocations;
    }

    private function gotoDefinition(TextDocument $document, ReflectionOffset $offset): TypeLocations
    {
        $nodeContext = $offset->nodeContext();

        if ($nodeContext instanceof MemberDeclarationContext) {
            return $this->gotoMethodDeclaration($nodeContext);
        }
        return match ($nodeContext->symbol()->symbolType()) {
            Symbol::METHOD, Symbol::PROPERTY, Symbol::CONSTANT, Symbol::CASE => $this->gotoMember($nodeContext),
            Symbol::CLASS_ => $this->gotoClass($nodeContext),
            Symbol::FUNCTION => $this->gotoFunction($nodeContext),
            Symbol::DECLARED_CONSTANT => $this->gotoDeclaredConstant($nodeContext),
            default => throw new CouldNotLocateDefinition(sprintf(
                'Do not know how to goto definition of symbol type "%s"',
                $nodeContext->symbol()->symbolType()
            )),
        };
    }

    private function gotoClass(NodeContext $nodeContext): TypeLocations
    {
        $className = $nodeContext->type();

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

        $path = $class->sourceCode()->uri();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for class "%s" has no path associated with it.',
                $class->name()
            ));
        }

        return new TypeLocations([new TypeLocation($className, new Location(
            TextDocumentUri::fromString($path),
            $class->position()
        ))]);
    }

    private function gotoFunction(NodeContext $nodeContext): TypeLocations
    {
        $functionName = $nodeContext->symbol()->name();

        try {
            $function = $this->reflector->reflectFunction($functionName);
        } catch (NotFound $e) {
            throw new CouldNotLocateDefinition($e->getMessage(), 0, $e);
        }

        $path = $function->sourceCode()->uri()?->path();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for function "%s" has no path associated with it.',
                $function->name()
            ));
        }

        return new TypeLocations([
            new TypeLocation(TypeFactory::unknown(), new Location(
                TextDocumentUri::fromString($path),
                $function->position()
            ))
        ]);
    }

    private function gotoDeclaredConstant(NodeContext $nodeContext): TypeLocations
    {
        $constantName = $nodeContext->symbol()->name();

        try {
            $constant = $this->reflector->reflectConstant($constantName);
        } catch (NotFound $e) {
            throw new CouldNotLocateDefinition($e->getMessage(), 0, $e);
        }

        $path = $constant->sourceCode()->uri()?->path();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for constant "%s" has no path associated with it.',
                $constant->name()
            ));
        }

        return new TypeLocations([
            new TypeLocation(TypeFactory::unknown(), new Location(
                TextDocumentUri::fromString($path),
                $constant->position()
            ))
        ]);
    }

    private function gotoMember(NodeContext $nodeContext): TypeLocations
    {
        $symbolName = $nodeContext->symbol()->name();
        $symbolType = $nodeContext->symbol()->symbolType();
        $containerType = $nodeContext->containerType();

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
                        if ($members->has($symbolName)) {
                            break;
                        }
                    }
                    assert($containingClass instanceof ReflectionClass || $containingClass instanceof ReflectionInterface || $containingClass instanceof ReflectionEnum);
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

            $path = $member->declaringClass()->sourceCode()->uri()?->path();

            if (null === $path) {
                throw new CouldNotLocateDefinition(sprintf(
                    'The source code for class "%s" has no path associated with it.',
                    (string) $containingClass->name()
                ));
            }

            $locations[] = new TypeLocation(
                $namedType,
                new Location(TextDocumentUri::fromString($path), $member->position())
            );
        }

        return new TypeLocations($locations);
    }

    private function gotoMethodDeclaration(MemberDeclarationContext $nodeContext): TypeLocations
    {
        try {
            $class = $this->reflector->reflectClass($nodeContext->classType()->name());
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
        })($nodeContext->name());

        if (null === $member) {
            return new TypeLocations([]);
        }


        $path = $member->declaringClass()->sourceCode()->uri()?->path();

        if (null === $path) {
            throw new CouldNotLocateDefinition(sprintf(
                'The source code for class "%s" has no path associated with it.',
                (string) $member->declaringClass()->name()
            ));
        }

        return new TypeLocations([new TypeLocation($nodeContext->classType(), new Location(
            TextDocumentUri::fromString($path),
            $member->position()
        ))]);
    }
}
