<?php

namespace Phpactor\ConfigLoader\Adapter\PathCandidate;

use Phpactor\ConfigLoader\Core\PathCandidate;
use RuntimeException;
use Symfony\Component\Filesystem\Path;

class AbsolutePathCandidate implements PathCandidate
{
    private string $absolutePath;

    public function __construct(
        string $absolutePath,
        private string $loader
    ) {
        $absolutePath = Path::canonicalize($absolutePath);
        $this->absolutePath = $absolutePath;

        if (!Path::isAbsolute($absolutePath)) {
            throw new RuntimeException(sprintf(
                'Path is not absolute "%s"',
                $absolutePath
            ));
        }
    }

    public function path(): string
    {
        return $this->absolutePath;
    }

    public function loader(): string
    {
        return $this->loader;
    }
}
