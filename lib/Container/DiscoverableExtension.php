<?php

namespace Phpactor\Container;

interface DiscoverableExtension extends OptionalExtension
{
    public function isSupported(): bool;
}
