<?php

namespace Phpactor\WorseReflection\Core\DocBlock;

use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

final class UnsupportedTagsParserFactory implements DocBlockFactory
{
    private const SUPPORTED_TAGS = [
        'property',
        'var',
        'param',
        'return',
        'method',
        'type',
        'deprecated',
        'extends',
        'implements',
        'template',
        'template-covariant',
        'template-extends',
        'mixin',
        'throws',
        'assert',
    ];

    public function __construct(
        private DocBlockFactory $innerFactory
    ) {
    }

    public function create(string $docblock, ReflectionScope $scope): DocBlock
    {
        if (trim($docblock) === '') {
            return new PlainDocblock();
        }

        // if no supported tags in the docblock, do not parse it
        if (0 === preg_match(
            sprintf('{@((psalm|phpstan|phan)-)?(%s)}', implode('|', self::SUPPORTED_TAGS)),
            $docblock,
            $matches
        )) {
            return new PlainDocblock($docblock);
        }

        return $this->innerFactory->create($docblock, $scope);
    }
}
