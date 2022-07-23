<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\InterfaceBaseClause;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ChainReflectionMemberCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionConstantCollection as CoreReflectionConstantCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionInterfaceCollection as CoreReflectionInterfaceCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;

use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\ServiceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\TypeResolver\ClassLikeTypeResolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface as CoreReflectionInterface;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMemberCollection;

class ReflectionInterface extends AbstractReflectionClass implements CoreReflectionInterface
{
    private ServiceLocator $serviceLocator;

    private InterfaceDeclaration $node;

    private SourceCode $sourceCode;

    private ?ReflectionInterfaceCollection $parents = null;

    private ?ReflectionMethodCollection $methods = null;

    /**
     * @var array<string, bool>
     */
    private array $visited;

    /**
     * @param array<string,bool> $visited
     */
    public function __construct(
        ServiceLocator $serviceLocator,
        SourceCode $sourceCode,
        InterfaceDeclaration $node,
        array $visited = []
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->node = $node;
        $this->sourceCode = $sourceCode;
        $this->visited = $visited;
    }

    /**
     * @return ReflectionMemberCollection<ReflectionMember>
     */
    public function members(): ReflectionMemberCollection
    {
        return ChainReflectionMemberCollection::fromCollections([
            $this->constants(),
            $this->methods()
        ]);
    }

    public function constants(): CoreReflectionConstantCollection
    {
        $parentConstants = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->constants() as $constant) {
                $parentConstants[$constant->name()] = $constant;
            }
        }

        $parentConstants = ReflectionConstantCollection::fromReflectionConstants($parentConstants);
        $constants = ReflectionConstantCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node, $this);

        return $parentConstants->merge($constants);
    }

    public function parents(): CoreReflectionInterfaceCollection
    {
        if ($this->parents) {
            return $this->parents;
        }

        $this->parents = ReflectionInterfaceCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node, $this->visited);

        return $this->parents;
    }

    public function isInstanceOf(ClassName $className): bool
    {
        if ($className == $this->name()) {
            return true;
        }

        // do not try and reflect the parents if we can locally see that it is
        // an instance of the given class
        $baseClause = $this->node->interfaceBaseClause;
        if ($baseClause instanceof InterfaceBaseClause) {
            if (NodeUtil::qualifiedNameListContains($baseClause->interfaceNameList, $className->__toString())) {
                return true;
            }
        }

        foreach ($this->parents() as $parent) {
            if ($parent->isInstanceOf($className)) {
                return true;
            }
        }

        return false;
    }

    public function methods(ReflectionClassLike $contextClass = null): CoreReflectionMethodCollection
    {
        if ($this->methods) {
            return $this->methods;
        }

        $parentMethods = [];
        foreach ($this->parents() as $parent) {
            foreach ($parent->methods($this)->byVisibilities([ Visibility::public(), Visibility::protected() ]) as $name => $method) {
                $parentMethods[$method->name()] = $method;
            }
        }

        $contextClass = $contextClass ?: $this;
        $parentMethods = ReflectionMethodCollection::fromReflectionMethods($parentMethods);
        $methods = ReflectionMethodCollection::fromInterfaceDeclaration($this->serviceLocator, $this->node, $contextClass);
        $merged = $parentMethods->merge($methods);
        assert($merged instanceof ReflectionMethodCollection);
        $this->methods = $merged;

        return $this->methods;
    }

    public function name(): ClassName
    {
        return ClassName::fromString((string) $this->node()->getNamespacedName());
    }

    public function sourceCode(): SourceCode
    {
        return $this->sourceCode;
    }

    public function docblock(): DocBlock
    {
        return $this->serviceLocator->docblockFactory()->create(
            $this->node()->getLeadingCommentAndWhitespaceText()
        )->withTypeResolver(new ClassLikeTypeResolver($this));
    }

    /**
     * @return InterfaceDeclaration
     */
    protected function node(): Node
    {
        return $this->node;
    }

    protected function serviceLocator(): ServiceLocator
    {
        return $this->serviceLocator;
    }
}
