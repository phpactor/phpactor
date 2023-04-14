<?php

namespace Phpactor\Extension\ObjectRenderer\Extension;

use Twig\Environment;

interface ObjectRendererTwigExtension
{
    public function configure(Environment $env): void;
}
