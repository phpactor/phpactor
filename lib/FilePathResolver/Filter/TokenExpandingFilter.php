<?php

namespace Phpactor\FilePathResolver\Filter;

use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\Filter;

class TokenExpandingFilter implements Filter
{
    /**
     * @var Expanders
     */
    private $expanders;

    public function __construct(Expanders $expanders)
    {
        $this->expanders = $expanders;
    }

    public function apply(string $path): string
    {
        if (false === strpos($path, '%')) {
            return $path;
        }

        if (!preg_match_all('{%(.*?)%}', $path, $matches)) {
            return $path;
        }

        foreach ($matches[1] as $match) {
            $expander = $this->expanders->get($match);
            $path = str_replace('%' . $match . '%', $expander->replacementValue(), $path);
        }

        return $path;
    }
}
