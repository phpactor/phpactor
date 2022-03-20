<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\TypeResolver;

use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\TokenKind;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Util\QualifiedNameListUtil;
use Phpactor\WorseReflection\Reflector;

class DeclaredMemberTypeResolver
{
    private const RESERVED_NAMES = [
        'iterable',
        'resource',
    ];

    private Reflector $reflector;

    public function __construct(Reflector $reflector)
    {
        $this->reflector = $reflector;
    }
    
    /**
     * @param mixed $declaredTypes
     */
    public function resolveTypes(Node $tolerantNode, $declaredTypes = null, ClassName $className = null, bool $nullable = false): Types
    {
        if (!$declaredTypes instanceof QualifiedNameList) {
            return Types::empty();
        }

        return Types::fromTypes(array_filter(array_map(function ($tolerantType = null) use ($tolerantNode, $className, $nullable) {
            if ($tolerantType instanceof Token && $tolerantType->kind === TokenKind::BarToken) {
                return false;
            }
            return $this->resolve($tolerantNode, $tolerantType, $className, $nullable);
        }, $declaredTypes->children)));
    }

    public function resolve(Node $tolerantNode, $tolerantType = null, ClassName $className = null, bool $nullable = false): Type
    {
        $type = $this->doResolve($tolerantType, $tolerantNode, $className);

        if ($nullable) {
            return TypeFactory::nullable($type);
        }
        return $type;
    }

    private function doResolve($tolerantType, ?Node $tolerantNode, ?ClassName $className): Type
    {
        if (null === $tolerantType) {
            return TypeFactory::undefined();
        }

        if ($tolerantType instanceof QualifiedNameList) {
            $tolerantType = QualifiedNameListUtil::firstQualifiedNameOrToken($tolerantType);
        }

        if ($tolerantType instanceof Token) {
            $text = $tolerantType->getText($tolerantNode->getFileContents());

            return TypeFactory::fromStringWithReflector((string)$text, $this->reflector);
        }

        $text = $tolerantType->getText($tolerantNode->getFileContents());
        if ($tolerantType->isUnqualifiedName() && in_array($text, self::RESERVED_NAMES)) {
            return TypeFactory::fromStringWithReflector($text, $this->reflector);
        }

        $name = $tolerantType->getResolvedName();
        if ($className && $name === 'self') {
            return TypeFactory::fromStringWithReflector((string) $className, $this->reflector);
        }

        return TypeFactory::fromStringWithReflector($name, $this->reflector);
    }
}
