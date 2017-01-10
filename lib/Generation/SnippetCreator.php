<?php

namespace Phpactor\Generation;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Phpactor\CodeContext;
use Phpactor\Generation\SnippetGeneratorRegistry;

class SnippetCreator
{
    /**
     * @var SnippetGeneratorRegistry
     */
    private $registry;

    public function __construct(SnippetGeneratorRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function create(CodeContext $codeContext, string $generatorName, array $options): string
    {
        $service = $this->registry->get($generatorName);
        $resolver = new OptionsResolver();
        $service->configureOptions($resolver);
        $options = $resolver->resolve($options);

        return $service->generate($codeContext, $options);
    }
}
