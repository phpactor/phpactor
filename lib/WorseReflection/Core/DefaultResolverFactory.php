<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Inference\FullyQualifiedNameResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Resolver\QualifiedNameResolver;
use Phpactor\WorseReflection\Reflector;

final class DefaultResolverFactory
{
    private Reflector $reflector;
    private FullyQualifiedNameResolver $nodeTypeConverter;

    public function __construct(
        Reflector $reflector,
        FullyQualifiedNameResolver $nodeTypeConverter
    )
    {
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
        ];
    }
}
