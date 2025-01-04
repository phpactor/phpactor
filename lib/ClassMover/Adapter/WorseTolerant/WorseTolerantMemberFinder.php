<?php

namespace Phpactor\ClassMover\Adapter\WorseTolerant;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassConstDeclaration;
use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\ClassMover\Domain\MemberFinder;
use Phpactor\ClassMover\Domain\Model\ClassMemberQuery;
use Phpactor\ClassMover\Domain\Model\Class_;
use Phpactor\ClassMover\Domain\Name\MemberName;
use Phpactor\ClassMover\Domain\Reference\MemberReference;
use Phpactor\ClassMover\Domain\Reference\MemberReferences;
use Phpactor\ClassMover\Domain\Reference\Position;
use Phpactor\ClassMover\Domain\SourceCode;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Bridge\TolerantParser\TextDocument\NodeToTextDocumentConverter;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionOffset;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WorseTolerantMemberFinder implements MemberFinder
{
    private Reflector $reflector;

    private Parser $parser;

    private LoggerInterface $logger;

    public function __construct(
        ?Reflector $reflector = null,
        ?Parser $parser = null,
        ?LoggerInterface $logger = null
    ) {
        $this->reflector = $reflector ?: ReflectorBuilder::create()->addSource(TextDocumentBuilder::empty());
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
     *
     * @return array<Node>
     */
    private function collectMemberReferences(Node $node, ClassMemberQuery $query): array
    {
        $memberNodes = [];
        $memberName = null;

        if (false === $query->hasType() || $query->type() === ClassMemberQuery::TYPE_METHOD) {
            $this->collectMethods($node, $query, $memberNodes);
        }

        if (false === $query->hasType() || $query->type() === ClassMemberQuery::TYPE_PROPERTY) {
            $this->collectProperties($node, $query, $memberNodes);
        }

        if (false === $query->hasType() || $query->type() === ClassMemberQuery::TYPE_CONSTANT) {
            $this->collectConstants($node, $query, $memberNodes);
        }

        foreach ($node->getChildNodes() as $childNode) {
            $memberNodes = array_merge($memberNodes, $this->collectMemberReferences($childNode, $query));
        }

        return $memberNodes;
    }

    /** @param array<Node> $memberNodes */
    private function collectMethods(Node $node, ClassMemberQuery $query, array &$memberNodes): void
    {
        if ($node instanceof MethodDeclaration) {
            $memberName = (string) $node->name?->getText($node->getFileContents());

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

    /** @param array<Node> $memberNodes */
    private function collectConstants(Node $node, ClassMemberQuery $query, array &$memberNodes): void
    {
        if ($node instanceof ClassConstDeclaration) {
            if ($node->constElements->children) {
                foreach ($node->constElements->getChildNodes() as $constElement) {
                    assert($constElement instanceof ConstElement);
                    $memberName = (string) $constElement->name->getText($constElement->getFileContents());
                    if ($query->matchesMemberName($memberName)) {
                        $memberNodes[] = $constElement;
                    }
                }
            }
        }

        if ($node instanceof ScopedPropertyAccessExpression && false === $node->parent instanceof CallExpression) {
            $memberName = (string) $node->memberName->getText($node->getFileContents());
            if ($query->matchesMemberName($memberName)) {
                $memberNodes[] = $node;
            }
        }
    }

    /** @param array<Node> $memberNodes */
    private function collectProperties(Node $node, ClassMemberQuery $query, array &$memberNodes): void
    {
        if ($node instanceof PropertyDeclaration) {
            if ($node->propertyElements->children) {
                foreach ($node->propertyElements->getChildNodes() as $propertyElement) {
                    if ($propertyElement instanceof AssignmentExpression) {
                        $propertyElement = $propertyElement->leftOperand;
                    }

                    if ($propertyElement instanceof Variable) {
                        $memberName = (string) $propertyElement->name->getText($propertyElement->getFileContents());
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
            $memberName = (string) $node->memberName->getText($node->getFileContents());

            // TODO: Some better way to determine if member names are properties
            if (str_starts_with($memberName, '$') && $query->matchesMemberName($memberName)) {
                $memberNodes[] = $node;
            }
        }
    }

    private function isMethodAccess(Node $node): bool
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

    private function getMemberDeclarationReference(?ReflectionClassLike $queryClass, Node $memberNode): ?MemberReference
    {
        assert($memberNode instanceof MethodDeclaration || $memberNode instanceof ConstElement || $memberNode instanceof Variable);
        // we don't handle Variable calls yet.
        if (false === $memberNode->name instanceof Token) {
            $this->logger->warning('Do not know how to infer method name from variable');
            return null;
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
            return null;
        }

        $className = ClassName::fromString($classNode->getNamespacedName());
        $reference = $reference->withClass(Class_::fromString($className));

        if (null === $queryClass) {
            return $reference;
        }

        if (null === $reflectionClass = $this->reflectClassLike($className)) {
            $this->logger->warning(sprintf('Could not find class "%s" for method declaration, ignoring it', (string) $className));
            return null;
        }

        // if the references class is not an instance of the requested class, or the requested class is not
        // an instance of the referenced class then ignore it.
        if ((!$reflectionClass instanceof ReflectionTrait) && false === $reflectionClass->isInstanceOf($queryClass->name())) {
            return null;
        }

        return $reference;
    }

    /**
     * Get static method call.
     * TODO: This does not support overridden static methods.
     */
    private function getScopedPropertyAccessReference(ClassMemberQuery $query, ScopedPropertyAccessExpression $memberNode): ?MemberReference
    {
        if ($memberNode->scopeResolutionQualifier instanceof Variable) {
            return null;
        }

        $memberNameToken = $memberNode->memberName;
        $startOffset = 0;
        if ($memberNameToken instanceof Variable) {
            $memberNameToken = $memberNameToken->name;
            $startOffset++; // do not include the $
        }

        if (false === $memberNameToken instanceof Token) {
            return null;
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
            NodeToTextDocumentConverter::convert($memberNode),
            ByteOffset::fromInt($memberNode->scopeResolutionQualifier->getEndPosition())
        );

        return $this->attachClassInfoToReference($reference, $query, $offset);
    }

    private function getMemberAccessReference(ClassMemberQuery $query, MemberAccessExpression $memberNode): ?MemberReference
    {
        /** @var Token|null */
        $memberName = $memberNode->memberName;
        if (false === $memberName instanceof Token) {
            $this->logger->warning('Do not know how to infer method name from variable');
            return null;
        }

        $reference = MemberReference::fromMemberNameAndPosition(
            MemberName::fromString((string) $memberNode->memberName->getText($memberNode->getFileContents())),
            Position::fromStartAndEnd(
                $memberNode->memberName->start,
                $memberNode->memberName->start + $memberNode->memberName->length
            )
        );

        $offset = $this->reflector->reflectOffset(
            NodeToTextDocumentConverter::convert($memberNode),
            ByteOffset::fromInt($memberNode->dereferencableExpression->getEndPosition())
        );

        return $this->attachClassInfoToReference($reference, $query, $offset);
    }

    private function reflectClassLike(ClassName $className): ?ReflectionClassLike
    {
        try {
            return $this->reflector->reflectClassLike($className);
        } catch (NotFound) {
            return null;
        }
    }

    private function resolveBaseReflectionClass(ClassMemberQuery $query): ?ReflectionClassLike
    {
        $queryClassReflection = $this->reflectClassLike(ClassName::fromString((string) $query->class()));

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

        if (!$queryClassReflection instanceof ReflectionClass) {
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
        $type = $offset->nodeContext()->type()->expandTypes()->classLike()->firstOrNull();

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


        $accepts = $type->instanceof(TypeFactory::reflectedClass($this->reflector, (string) $query->class()));

        if ($accepts->isMaybe()) {
            return $reference;
        }
        if ($accepts->isFalse()) {
            return null;
        }

        return $reference->withClass(Class_::fromString((string) $type->name()->full()));
    }

    private function memberStartPosition(Node $memberNode): int
    {
        assert($memberNode instanceof MethodDeclaration || $memberNode instanceof ConstElement || $memberNode instanceof Variable);
        $name = $memberNode->name;
        assert($name !== null);
        $start = $name->start;

        if ($memberNode->getFirstAncestor(PropertyDeclaration::class)) {
            return $start + 1; // ignore the dollar sign
        }

        return $start;
    }
}
