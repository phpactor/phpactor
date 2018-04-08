<?php

namespace Phpactor\Extension;

use Psr\Container\ContainerInterface;
use Closure;

interface Container extends ContainerInterface
{
    public function getServiceIdsForTag(string $tag): array;

    public function getParameter(string $name);

    public function getParameters(): array;
}
