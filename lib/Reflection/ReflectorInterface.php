<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phpactor\Reflection;

use BetterReflection\Reflection\ReflectionClass;

interface ReflectorInterface
{
    public function reflectFile(string $file): ReflectionClass;

    public function reflectClass(string $classFqn): ReflectionClass;
}
