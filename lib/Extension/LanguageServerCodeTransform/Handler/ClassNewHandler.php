<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\Handler;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\LanguageServer\Core\Handler\Handler;

class ClassNewHandler implements Handler
{
    public const METHOD_VARIANTS = 'phpactor/classnew/variants';

    public function __construct(
        private Generators $generators,
    ) {
    }

    /**
     * @return Promise<string[]>
     */
    public function listVariants(): Promise
    {
        return new Success($this->generators->names());
    }

    public function methods(): array
    {
        return [
            self::METHOD_VARIANTS => 'listVariants',
        ];
    }
}
