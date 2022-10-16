<?php

namespace Phpactor\Extension\Symfony\WorseReflection;

use Phpactor\Extension\Symfony\Model\SymfonyContainerInspector;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;

class SymfonyContainerContextResolver implements MemberContextResolver
{
    const CONTAINER_CLASS = 'Symfony\\Component\\DependencyInjection\\ContainerInterface';

    private SymfonyContainerInspector $inspector;

    public function __construct(SymfonyContainerInspector $inspector)
    {
        $this->inspector = $inspector;
    }

    public function resolveMemberContext(
        string $memberType,
        string $memberName,
        Type $containerType,
        FunctionArguments $arguments
    ): ?Type
    {
        if ($memberType !== ReflectionMember::TYPE_METHOD) {
            return null;
        }

        if ($memberName !== 'get') {
            return null;
        }

        if (count($arguments) === 0) {
            return null;
        }

        if ($containerType->instanceof(TypeFactory::class(self::CONTAINER_CLASS))->isFalseOrMaybe()) {
            return null;
        }

        $argument = $arguments->at(0)->type();
        if ($argument instanceof StringLiteralType) {
            $service = $this->inspector->service($argument->value());
            if (null === $service) {
                return TypeFactory::union(TypeFactory::object(), TypeFactory::null());
            }
            return $service->type;
        }
        if ($argument instanceof ClassStringType && $argument->className()) {
            $service = $this->inspector->service($argument->className()->__toString());
            if (null === $service) {
                return TypeFactory::union(TypeFactory::object(), TypeFactory::null());
            }
            return $service->type;
        }

        return TypeFactory::undefined();
    }
}
