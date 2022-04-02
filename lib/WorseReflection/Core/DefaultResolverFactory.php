<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node\ConstElement;
use Microsoft\PhpParser\Node\DelimitedList\QualifiedNameList;
use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ConstElementResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\EnumCaseDeclarationResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\ParameterResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\QualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\QualifiedNameListResolver;
use Phpactor\WorseReflection\Reflector;

final class DefaultResolverFactory
{
    private Reflector $reflector;

    private FullyQualifiedNameResolver $nodeTypeConverter;

    public function __construct(
        Reflector $reflector,
        FullyQualifiedNameResolver $nodeTypeConverter
    ) {
        $this->reflector = $reflector;
        $this->nodeTypeConverter = $nodeTypeConverter;
    }

    /**
     * @return array<class-string,Resolver>
     */
    public function createResolvers(): array
    {
        return [
            QualifiedName::class => new QualifiedNameResolver($this->reflector, $this->nodeTypeConverter),
            QualifiedNameList::class => new QualifiedNameListResolver(),
            ConstElement::class => new ConstElementResolver(),
            EnumCaseDeclaration::class => new EnumCaseDeclarationResolver(),
            Parameter::class => new ParameterResolver(),
        ];
    }
}
