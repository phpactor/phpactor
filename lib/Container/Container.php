<?php

namespace Phpactor\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function getServiceIdsForTag(string $tag): array;

    public function getParameter(string $name);

    public function getParameters(): array;
}
