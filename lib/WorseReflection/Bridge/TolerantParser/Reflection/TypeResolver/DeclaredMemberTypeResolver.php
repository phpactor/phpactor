<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver;

use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\SelfType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class DeclaredMemberTypeResolver
{
    public function __construct(private Reflector $reflector)
    {
    }

    /**
     * @param mixed $declaredTypes
     */
    public function resolveTypes(Node $tolerantNode, $declaredTypes = null, ?ClassName $className = null, bool $nullable = false): Type
    {
        if (!$declaredTypes instanceof QualifiedNameList) {
            return TypeFactory::undefined();
        }

        $type = NodeUtil::typeFromQualifiedNameLike($this->reflector, $tolerantNode, $declaredTypes, $className);

        if (!$nullable) {
            return $type;
        }

        return TypeFactory::nullable($type);
    }

    /**
     * @param null|Node|Token $tolerantType
     */
    public function resolve(Node $tolerantNode, $tolerantType = null, ?ClassName $className = null, bool $nullable = false): Type
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
    private function doResolve($tolerantType, ?Node $tolerantNode, ?ClassName $className = null): Type
    {
        if (null === $tolerantType) {
            return TypeFactory::undefined();
        }

        $type =  NodeUtil::typeFromQualifiedNameLike($this->reflector, $tolerantNode, $tolerantType, $className);

        $type = $type->map(function (Type $type) use ($className) {
            if ($className && $type instanceof SelfType) {
                return new SelfType(TypeFactory::reflectedClass($this->reflector, $className));

            }
            return $type;
        });

        return $type;
    }
}
