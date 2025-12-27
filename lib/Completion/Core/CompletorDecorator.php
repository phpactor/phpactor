<?php

namespace Phpactor\Completion\Core;

interface CompletorDecorator
{
    /**
     * @return class-string
     */
    public function decorates(): string;
}
