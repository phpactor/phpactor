<?php

namespace Phpactor\Extension\CodeTransform\Application;

use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\CodeTransform\Domain\SourceCode;
use Webmozart\PathUtil\Path;

class Transformer
{
    /**
     * @var CodeTransform
     */
    private $transform;

    /**
     * @var FilesystemHelper
     */
    private $filesystemHelper;

    public function __construct(
        CodeTransform $transform
    ) {
        $this->transform = $transform;
        $this->filesystemHelper = new FilesystemHelper();
    }

    public function transform($source, array $transformations)
    {
        if (file_exists($source)) {
            $source = Path::makeAbsolute($source, getcwd());
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
