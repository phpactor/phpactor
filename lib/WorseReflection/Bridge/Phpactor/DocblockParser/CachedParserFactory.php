<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\WorseReflection\Core\Cache;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

class CachedParserFactory implements DocBlockFactory
{
    public function __construct(
        private readonly DocBlockFactory $innerFactory,
        private readonly Cache $cache
    ) {
    }

    public function create(string $docblock, ReflectionScope $scope): DocBlock
    {
        if (!trim($docblock)) {
            return new PlainDocblock('');
        }

        return $this->cache->getOrSet('docblock_' . $docblock, function () use ($docblock, $scope) {
            return $this->innerFactory->create($docblock, $scope);
        });
    }
}
