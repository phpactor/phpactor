<?php

namespace Phpactor\Extension\Php\Status;

use Phpactor\Extension\LanguageServer\Status\StatusProvider;
use Phpactor\Extension\Php\Model\ChainResolver;
use Phpactor\Extension\Php\Model\RuntimePhpVersionResolver;

class PhpStatusProvider implements StatusProvider
{
    public function __construct(
        private ChainResolver $chainResolver,
    ) {
    }

    public function title(): string
    {
        return 'php';
    }

    public function provide(): array
    {
        return [
            'project' => $this->chainResolver->resolve(),
            'source' => $this->chainResolver->source(),
            'runtime' => phpversion(),
        ];
    }
}
