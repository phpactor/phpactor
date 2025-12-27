<?php

namespace Phpactor\CodeBuilder\Adapter\Twig;

use Phpactor\CodeBuilder\Adapter\WorseReflection\TypeRenderer\WorseTypeRenderer;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\WorseReflection\Core\Type as PhpactorType;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Phpactor\CodeBuilder\Util\TextFormat;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly TextFormat $textFormat,
        private readonly WorseTypeRenderer $typeRenderer
    ) {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('indent', [ $this, 'indent' ]),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_type', function (Type $type) {
                $originalType = $type->originalType();
                if ($originalType instanceof PhpactorType) {
                    return $this->typeRenderer->render($originalType);
                }
                return $type->__toString();
            }),
        ];
    }


    public function indent(string $string, int $level = 0): string
    {
        return $this->textFormat->indent($string, $level);
    }
}
