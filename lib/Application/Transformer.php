<?php

namespace Phpactor\Application;

use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Application\Helper\FilesystemHelper;
use Phpactor\CodeTransform\Domain\SourceCode;

class Transformer
{
    private $transform;

    /**
     * @var FilesystemHelper
     */
    private $filesystemHelper;

    public function __construct(
        CodeTransform $transform
    )
    {
        $this->transform = $transform;
        $this->filesystemHelper = new FilesystemHelper();
    }

    public function transform(string $src, array $transformations)
    {
        $code = $this->filesystemHelper->contentsFromFileOrStdin($src);

        $code = SourceCode::fromString($code);
        $transformedCode = $this->transform->transform($code, $transformations);

        if ($code == $transformedCode) {
            return null;
        }

        return $transformedCode;
    }
}
