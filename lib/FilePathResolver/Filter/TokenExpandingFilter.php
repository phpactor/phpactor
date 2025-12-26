<?php

namespace Phpactor\FilePathResolver\Filter;

use Phpactor\FilePathResolver\Expanders;
use Phpactor\FilePathResolver\Filter;

class TokenExpandingFilter implements Filter
{
    public function __construct(private readonly Expanders $expanders)
    {
    }

    public function apply(string $path): string
    {
        if (!str_contains($path, '%')) {
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
