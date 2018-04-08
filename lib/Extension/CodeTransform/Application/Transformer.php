<?php

namespace Phpactor\Extension\CodeTransform\Application;

use Phpactor\CodeTransform\CodeTransform;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\CodeTransform\Domain\SourceCode;

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
