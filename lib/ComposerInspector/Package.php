<?php

namespace Phpactor\ComposerInspector;

class Package
{
    public function __construct(
        public string $name,
        public string $version,
        public bool $isDev
    ) {
    }
}
