<?php

namespace Phpactor\Extension\CodeTransformExtra\Application;

use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\CodeTransform\Domain\SourceCode;
use Symfony\Component\Filesystem\Path;

class Transformer
{
    private readonly FilesystemHelper $filesystemHelper;

    public function __construct(
        private readonly CodeTransform $transform
    ) {
        $this->filesystemHelper = new FilesystemHelper();
    }

    public function transform($source, array $transformations)
    {
        if (file_exists($source)) {
            /** @var string $workDir */
            $workDir = getcwd();
            $source = Path::makeAbsolute($source, $workDir);
            $source = SourceCode::fromStringAndPath(file_get_contents($source), $source);
        }

        if (!$source instanceof SourceCode) {
            $source = $this->filesystemHelper->contentsFromFileOrStdin($source);
            $source = SourceCode::fromString($source);
        }

        $transformedCode = $this->transform->transform($source, $transformations);

        return $transformedCode;
    }
}
