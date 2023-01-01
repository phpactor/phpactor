<?php

namespace Phpactor\ClassMover\Adapter\WorseTolerant;

use Phpactor\ClassMover\Domain\MemberFinder;
use Phpactor\ClassMover\Domain\Reference\MemberReferences;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode as WorseSourceCode;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\ClassMover\Domain\Reference\MemberReference;
use Phpactor\ClassMover\Domain\Reference\Position;
use Phpactor\WorseReflection\Core\Offset;
use Phpactor\ClassMover\Domain\Model\Class_;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\ClassMover\Domain\Name\MemberName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\WorseReflection\ReflectorBuilder;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;

class WorseTolerantMemberFinder implements MemberFinder
{
    private Reflector $reflector;

    private Parser $parser;

    private LoggerInterface $logger;

    public function __construct(
        Reflector $reflector = null,
        Parser $parser = null,
        LoggerInterface $logger = null
    ) {
        $this->reflector = $reflector ?: ReflectorBuilder::create()->addSource(WorseSourceCode::fromString(''));
        $this->parser = $parser ?: new Parser();
        $this->logger = $logger ?: new NullLogger();
    }

    public function findMembers(SourceCode $source, ClassMemberQuery $query): MemberReferences
    {
        $rootNode = $this->parser->parseSourceFile((string) $source);
        $memberNodes = $this->collectMemberReferences($rootNode, $query);

        $queryClassReflection = null;
        // TODO: Factor this to a method
        if ($query->hasClass()) {
            $queryClassReflection = $this->resolveBaseReflectionClass($query);
        }

        $references = [];
        foreach ($memberNodes as $memberNode) {
            if ($memberNode instanceof ScopedPropertyAccessExpression && $reference = $this->getScopedPropertyAccessReference($query, $memberNode)) {
                $references[] = $reference;
                continue;
            }

            if ($memberNode instanceof MemberAccessExpression && $reference = $this->getMemberAccessReference($query, $memberNode)) {
                $references[] = $reference;
                continue;
            }

            if ($memberNode instanceof MethodDeclaration && $reference = $this->getMemberDeclarationReference($queryClassReflection, $memberNode)) {
                $references[] = $reference;
                continue;
            }

            // properties ...
            if ($memberNode instanceof Variable && $reference = $this->getMemberDeclarationReference($queryClassReflection, $memberNode)) {
                $references[] = $reference;
                continue;
            }

            if ($memberNode instanceof ConstElement && $reference = $this->getMemberDeclarationReference($queryClassReflection, $memberNode)) {
                $references[] = $reference;
                continue;
            }
        }

        return MemberReferences::fromMemberReferences($references)->unique();
    }

    /**
     * Collect all nodes which reference the method NAME.
     * We will check if they belong to the requested class later.
     */
    private function collectMemberReferences(Node $node, ClassMemberQuery $query): array
    {
        $memberNodes = [];
        $memberName = null;

        if (false === $query->hasType() || $query->type() === ClassMemberQuery::TYPE_METHOD) {
            if ($node instanceof MethodDeclaration) {
                $memberName = $node->name->getText($node->getFileContents());

                if ($query->matchesMemberName($memberName)) {
                    $memberNodes[] = $node;
                }
            }

            if ($this->isMethodAccess($node)) {
                assert($node instanceof CallExpression);
                $callableExpression = $node->callableExpression;
                assert($callableExpression instanceof ScopedPropertyAccessExpression || $callableExpression instanceof MemberAccessExpression);
                $memberName = $callableExpression->memberName->getText($node->getFileContents());

                if ($query->matchesMemberName($memberName)) {
                    $memberNodes[] = $node->callableExpression;
                }
            }
        }

        if (false === $query->hasType() || $query->type() === ClassMemberQuery::TYPE_PROPERTY) {
            /** @var PropertyDeclaration $node */
            if ($node instanceof PropertyDeclaration) {
                if ($node->propertyElements->children) {
                    foreach ($node->propertyElements->getChildNodes() as $propertyElement) {
                        if ($propertyElement instanceof AssignmentExpression) {
                            $propertyElement = $propertyElement->leftOperand;
                        }

                        if ($propertyElement instanceof Variable) {
                            $memberName = $propertyElement->name->getText($propertyElement->getFileContents());
                            if ($query->matchesMemberName($memberName)) {
                                $memberNodes[] = $propertyElement;
                            }
                        }
                    }
                }
            }

            // property access - only if it is not part of a call() expression
            if ($node instanceof MemberAccessExpression && false === $node->parent instanceof CallExpression) {
                $memberName = $node->memberName->getText($node->getFileContents());
                if (is_string($memberName) && $query->matchesMemberName($memberName)) {
                    $memberNodes[] = $node;
                }
            }

            if ($node instanceof ScopedPropertyAccessExpression && false === $node->parent instanceof CallExpression) {
                $memberName = $node->memberName->getText($node->getFileContents());

                // TODO: Some better way to determine if member names are properties
                if (substr($memberName, 0, 1) == '$' && $query->matchesMemberName($memberName)) {
                    $memberNodes[] = $node;
                }
            }
        }

        if (false === $query->hasType() || $query->type() === ClassMemberQuery::TYPE_CONSTANT) {
            if ($node instanceof ClassConstDeclaration) {
                if ($node->constElements->children) {
                    foreach ($node->constElements->getChildNodes() as $constElement) {
                        assert($constElement instanceof ConstElement);
                        $memberName = $constElement->name->getText($constElement->getFileContents());
                        if ($query->matchesMemberName($memberName)) {
                            $memberNodes[] = $constElement;
                        }
                    }
                }
            }

            if ($node instanceof ScopedPropertyAccessExpression && false === $node->parent instanceof CallExpression) {
                $memberName = $node->memberName->getText($node->getFileContents());
                if ($query->matchesMemberName($memberName)) {
                    $memberNodes[] = $node;
                }
            }
        }

        foreach ($node->getChildNodes() as $childNode) {
            $memberNodes = array_merge($memberNodes, $this->collectMemberReferences($childNode, $query));
        }

        return $memberNodes;
    }

    private function isMethodAccess(Node $node)
    {
        if (false === $node instanceof CallExpression) {
            return false;
        }

        if (null === $node->callableExpression) {
            return false;
        }

        return
            $node->callableExpression instanceof MemberAccessExpression ||
            $node->callableExpression instanceof ScopedPropertyAccessExpression;
    }

    private function getMemberDeclarationReference(ReflectionClassLike $queryClass = null, Node $memberNode)
    {
        assert($memberNode instanceof MethodDeclaration || $memberNode instanceof ConstElement || $memberNode instanceof Variable);
        // we don't handle Variable calls yet.
        if (false === $memberNode->name instanceof Token) {
            $this->logger->warning('Do not know how to infer method name from variable');
            return;
        }

        $memberName = MemberName::fromString((string) $memberNode->name->getText($memberNode->getFileContents()));
        $reference = MemberReference::fromMemberNameAndPosition(
            $memberName,
            Position::fromStartAndEnd(
                $this->memberStartPosition($memberNode),
                $memberNode->name->start + $memberNode->name->length - 1
            )
        );

        /** @var ClassDeclaration|InterfaceDeclaration|TraitDeclaration|null $classNode */
        $classNode = $memberNode->getFirstAncestor(ClassDeclaration::class, InterfaceDeclaration::class, TraitDeclaration::class);

        // if no class node found, then this is not valid, don't know how to reproduce this, probably
        // not a possible scenario with the parser.
        if (null === $classNode) {
            return;
        }

        $className = ClassName::fromString($classNode->getNamespacedName());
        $reference = $reference->withClass(Class_::fromString($className));

        if (null === $queryClass) {
            return $reference;
        }

        if (null === $reflectionClass = $this->reflectClass($className)) {
            $this->logger->warning(sprintf('Could not find class "%s" for method declaration, ignoring it', (string) $className));
            return;
        }

        // if the references class is not an instance of the requested class, or the requested class is not
        // an instance of the referenced class then ignore it.
        if (false === $reflectionClass->isTrait() && false === $reflectionClass->isInstanceOf($queryClass->name())) {
            return;
        }

        return $reference;
    }

    /**
     * Get static method call.
     * TODO: This does not support overridden static methods.
     */
    private function getScopedPropertyAccessReference(ClassMemberQuery $query, ScopedPropertyAccessExpression $memberNode)
    {
        if ($memberNode->scopeResolutionQualifier instanceof Variable) {
            return;
        }

        $memberNameToken = $memberNode->memberName;
        $startOffset = 0;
        if ($memberNameToken instanceof Variable) {
            $memberNameToken = $memberNameToken->name;
            $startOffset++; // do not include the $
        }

        if (false === $memberNameToken instanceof Token) {
            return;
        }

        $memberName = (string) $memberNameToken->getText($memberNode->getFileContents());

        $reference = MemberReference::fromMemberNameAndPosition(
            MemberName::fromString($memberName),
            Position::fromStartAndEnd(
                $memberNameToken->start + $startOffset,
                $memberNameToken->start + $memberNameToken->length
            )
        );

        $offset = $this->reflector->reflectOffset(
            WorseSourceCode::fromString($memberNode->getFileContents()),
            Offset::fromInt($memberNode->scopeResolutionQualifier->getEndPosition())
        );

        return $this->attachClassInfoToReference($reference, $query, $offset);
    }

    private function getMemberAccessReference(ClassMemberQuery $query, MemberAccessExpression $memberNode)
    {
        /** @var Token|null */
        $memberName = $memberNode->memberName;
        if (false === $memberName instanceof Token) {
            $this->logger->warning('Do not know how to infer method name from variable');
            return;
        }

        $reference = MemberReference::fromMemberNameAndPosition(
            MemberName::fromString((string) $memberNode->memberName->getText($memberNode->getFileContents())),
            Position::fromStartAndEnd(
                $memberNode->memberName->start,
                $memberNode->memberName->start + $memberNode->memberName->length
            )
        );

        $offset = $this->reflector->reflectOffset(
            WorseSourceCode::fromString($memberNode->getFileContents()),
            Offset::fromInt($memberNode->dereferencableExpression->getEndPosition())
        );

        return $this->attachClassInfoToReference($reference, $query, $offset);
    }

    private function reflectClass(ClassName $className)
    {
        try {
            return $this->reflector->reflectClassLike($className);
        } catch (NotFound) {
            return null;
        }
    }

    private function resolveBaseReflectionClass(ClassMemberQuery $query): ?ReflectionClassLike
    {
        $queryClassReflection = $this->reflectClass(ClassName::fromString((string) $query->class()));

        if (null === $queryClassReflection) {
            return $queryClassReflection;
        }

        $methods = $queryClassReflection->methods();

        if (false === $query->hasMember()) {
            return $queryClassReflection;
        }

        if (false === $methods->has($query->memberName())) {
            return $queryClassReflection;
        }

        if (false === $queryClassReflection->isClass()) {
            return $queryClassReflection;
        }

        // TODO: Support the case where interfaces both implement the same method
        foreach ($queryClassReflection->interfaces() as $interfaceReflection) {
            if ($interfaceReflection->methods()->has($query->memberName())) {
                $queryClassReflection = $interfaceReflection;
                break;
            }
        }

        return $queryClassReflection;
    }

    private function attachClassInfoToReference(MemberReference $reference, ClassMemberQuery $query, ReflectionOffset $offset): ?MemberReference
    {
        $type = $offset->symbolContext()->type()->toTypes()->classLike()->firstOrNull();

        if ($query->hasMember() && !$type) {
            return $reference;
        }
        if (!$type instanceof ReflectedClassType) {
            return null;
        }

        if (false === $query->hasClass()) {
            $reference = $reference->withClass(Class_::fromString((string) $type->name()->full()));
            return $reference;
        }


        $accepts = $type->instanceof(TypeFactory::reflectedClass($this->reflector, $query->class()->__toString()));

        if ($accepts->isMaybe()) {
            return $reference;
        }
        if ($accepts->isFalse()) {
            return null;
        }

        return $reference->withClass(Class_::fromString((string) $type->name()->full()));
    }

    private function memberStartPosition(Node $memberNode)
    {
        assert($memberNode instanceof MethodDeclaration || $memberNode instanceof ConstElement || $memberNode instanceof Variable);
        $start = $memberNode->name->start;

        if ($memberNode->getFirstAncestor(PropertyDeclaration::class)) {
            return $start + 1; // ignore the dollar sign
        }

        return $start;
    }
}
