<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Phpactor\CodeBuilder\Util\TextFormat;

class TwigExtension extends AbstractExtension
{
    private TwigRenderer $generator;

    private TextFormat $textFormat;

    public function __construct(TwigRenderer $generator, TextFormat $textFormat = null)
    {
        $this->generator = $generator;
        $this->textFormat = $textFormat ?: new TextFormat('    ');
    }

    public function getFilters()
    {
        return [
            new TwigFilter('indent', [ $this, 'indent' ]),
        ];
    }

    public function indent(string $string, int $level = 0)
    {
        return $this->textFormat->indent($string, $level);
    }
}
