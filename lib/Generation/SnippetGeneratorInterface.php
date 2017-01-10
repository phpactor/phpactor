<?php

namespace Phpactor\Generation;

use Phpactor\CodeContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface SnippetGeneratorInterface
{
    public function generate(CodeContext $codeContext, array $options): string;

    public function configureOptions(OptionsResolver $resolver);
}
