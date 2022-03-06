<?php

namespace Phpactor\Extension\ExtensionManager\Model;

interface ExtensionConfig
{
    public function require(string $extension, string $version): void;

    public function unrequire(string $extension): void;

    public function revert(): void;

    public function write(): void;
}
