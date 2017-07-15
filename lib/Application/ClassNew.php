<?php

namespace Phpactor\Application;

use Phpactor\CodeTransform\Domain\GenerateNew;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Filesystem\Domain\FilePath;

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

    public function availableGenerators()
    {
        return $this->generators->names();
    }

    public function generate(string $src, string $variant = 'default', bool $overwrite = false)
    {
        $className = $this->normalizer->normalizeToClass($src);

        $code = $this->generators->get($variant)->generateNew(ClassName::fromString((string) $className));
        $filePath = $this->normalizer->normalizeToFile($className);

        if (false === $overwrite && file_exists($filePath) && 0 !== filesize($filePath)) {
            throw new Exception\FileAlreadyExists(sprintf('File "%s" already exists and is non-empty', $filePath));
        }

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        file_put_contents(FilePath::fromString($filePath), (string) $code);

        return (string) $filePath;
    }
}



