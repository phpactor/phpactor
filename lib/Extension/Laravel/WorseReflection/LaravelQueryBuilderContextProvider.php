<?php

namespace Phpactor\Extension\Laravel\WorseReflection;

use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\Resolver\MemberAccess\MemberContextResolver;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Reflector;

class LaravelQueryBuilderContextProvider implements MemberContextResolver
{
    public function __construct(private LaravelContainerInspector $inspector)
    {
    }

    public function resolveMemberContext(Reflector $reflector, ReflectionMember $member, Type $type, ?FunctionArguments $arguments): ?Type
    {
        return $type;
        // @todo : At this point type is ok.
        //
        /* if ($member->name() === 'players') { */
        /*     dump($type->__toString()); */
        /*     dump($member->name()); */
        /* } */
        /* // @todo: Supported methods,where, whereLike etc. */
        /* if ($member->name() === 'where' && $member->memberType() === 'method') { */
        /*     dump($member->position()); // This is always the same. */
        /*     // Todo: Check if in a closure. */
        /*     dump('PARENT:'); */
        /*     dump($member->memberType()); */
        /*  */
        /*     $class = TypeFactory::class(ClassName::fromString('\\Illuminate\\Database\\Eloquent\\Builder'), $reflector); */
        /*  */
        /*     $reflectionClass = $reflector->reflectClass(ClassName::fromString('\\Illuminate\\Database\\Eloquent\\Builder')); */
        /*  */
        /*     $newCollection = ReflectionMethodCollection::fromReflectionMethods([ */
        /*         new VirtualReflectionMethod( */
        /*             $reflectionClass->position(), */
        /*             $reflectionClass, */
        /*             $reflectionClass, */
        /*             'DEMO', */
        /*             new Frame(), */
        /*             $member->docblock(), */
        /*             $member->scope(), */
        /*             Visibility::public(), */
        /*             new StringType(), */
        /*             new StringType(), */
        /*             ReflectionParameterCollection::fromReflections($reflections), */
        /*             NodeText::fromString('bar'), */
        /*             false, */
        /*             false, */
        /*             new Deprecation(false), */
        /*         ), */
        /*     ]); */
        /*     return $class->mergeMembers($newCollection); */
        /* } */
        return TypeFactory::undefined();
    }
}
