<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver;

use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class DeclaredMemberTypeResolver
{
    private Reflector $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }
    
    /**
     * @param mixed $declaredTypes
     */
    public function resolveTypes(Node $tolerantNode, $declaredTypes = null, ClassName $className = null, bool $nullable = false): Type
    {
        if (!$declaredTypes instanceof QualifiedNameList) {
            return TypeFactory::undefined();
        }

        $type = NodeUtil::typeFromQualfiedNameLike($this->reflector, $tolerantNode, $declaredTypes);

        if (!$nullable) {
            return $type;
        }

        return TypeFactory::nullable($type);
    }

    /**
     * @param null|Node|Token $tolerantType
     */
    public function resolve(Node $tolerantNode, $tolerantType = null, ClassName $className = null, bool $nullable = false): Type
    {
        $type = $this->doResolve($tolerantType, $tolerantNode, $className);

        if ($nullable) {
            return TypeFactory::nullable($type);
        }
        return $type;
    }

    /**
     * @param null|Node|Token $tolerantType
     */
    private function doResolve($tolerantType, ?Node $tolerantNode, ?ClassName $className): Type
    {
        if (null === $tolerantType) {
            return TypeFactory::undefined();
        }

        return NodeUtil::typeFromQualfiedNameLike($this->reflector, $tolerantNode, $tolerantType);
    }
}
