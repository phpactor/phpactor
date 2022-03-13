<?php

namespace Phpactor\ConfigLoader\Adapter\PathCandidate;

use Phpactor\ConfigLoader\Core\PathCandidate;
use RuntimeException;
use Webmozart\PathUtil\Path;

class AbsolutePathCandidate implements PathCandidate
{
    private string $absolutePath;

    private string $loader;

    public function __construct(string $absolutePath, string $loader)
    {
        $absolutePath = Path::canonicalize($absolutePath);
        $this->absolutePath = $absolutePath;
        $this->loader = $loader;

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
