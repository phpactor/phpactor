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

    public function transform($source, array $transformations)
    {
        if (!$source instanceof SourceCode) {
            $source = $this->filesystemHelper->contentsFromFileOrStdin($source);
            $source = SourceCode::fromString($source);
        }

        $transformedCode = $this->transform->transform($source, $transformations);

        if ($source == $transformedCode) {
            return null;
        }

        return $transformedCode;
    }
}
