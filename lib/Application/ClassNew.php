<?php

namespace Phpactor\Application;

use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\Generators;

class ClassNew
{
    /**
     * @var ClassFileNormalizer
     */
    private $normalizer;

    /**
     * @var GenerateNew
     */
    private $generators;

    public function __construct(ClassFileNormalizer $normalizer, Generators $generators)
    {
        $this->normalizer = $normalizer;
        $this->generators = $generators;
    }

    public function generate(string $src, string $variant = 'default')
    {
        $className = $this->normalizer->normalizeToClass($src);

        return $this->generators->get($variant)->generateNew(ClassName::fromString((string) $className));
    }
}


