<?php

namespace Phpactor\Container;

interface OptionalExtension extends Extension
{
    /**
     * Return a short name for the extension which can be used to reference
     * this extension.
     *
     * Extensions implementing this class can be enabled or disabled
     */
    public function name(): string;
}
