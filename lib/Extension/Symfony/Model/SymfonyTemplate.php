<?php

namespace Phpactor\Extension\Symfony\Model;

final class SymfonyTemplate
{
    /**
     * @param array<string> $params
     * TODO: maybe add/guess the type of the param
     * TODO: provide the params for autocompletion in twig
     * (this will be probably hard, maybe impossible
     * as it would need attaching the lsp into the .twig file)
     */
    public function __construct(
        public string $path,
        public array $params = []
    ) {
    }
}
