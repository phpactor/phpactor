<?php

namespace Phpactor\Extension\ExtensionManager\Service;

interface ProgressLogger
{
    public function log(string $message): void;
}
