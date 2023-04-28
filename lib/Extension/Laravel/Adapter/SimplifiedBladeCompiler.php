<?php

namespace Phpactor\Extension\Laravel\Adapter;

use Illuminate\View\Compilers\BladeCompiler;

class SimplifiedBladeCompiler extends BladeCompiler
{
    const NEW_CONSTANT = 'Malformed @forelse statement.';

    protected function compileForelse($expression): string
    {
        $matches = [];
        preg_match('/\( *(.+) +as +(.+)\)$/is', $expression ?? '', $matches);

        if (count($matches) === 0) {
            return '';
        }

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        return "<?php foreach({$iteratee} as {$iteration}): \$loop = new stdClass(); ?>";
    }

    protected function compileForeach($expression): string
    {
        $matches = [];
        preg_match('/\( *(.+) +as +(.*)\)$/is', $expression ?? '', $matches);

        if (count($matches) === 0) {
            return '';
        }

        $iteratee = trim($matches[1]);

        $iteration = trim($matches[2]);

        return "<?php foreach({$iteratee} as {$iteration}): \$loop = new stdClass(); ?>";
    }
}
