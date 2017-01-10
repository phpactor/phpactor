<?php

declare(strict_types=1);

namespace Phpactor\Composer;

final class ClassFqn
{
    private $namespace = '';
    private $shortName;

    private function __construct()
    {
    }

    public static function fromString(string $classFqn)
    {
        if (0 === strpos($classFqn, '\\')) {
            $classFqn = substr($classFqn, 1);
        }

        if (substr($classFqn, -1) === '\\') {
            throw new \InvalidArgumentException(sprintf(
                'Trailing slash detected in class name "%s"',
                $classFqn
            ));
        }

        $pos = strrpos($classFqn, '\\');

        $instance = new self();

        if (false === $pos) {
            $instance->shortName = $classFqn;
            return $instance;
        }

        $instance->namespace = substr($classFqn, 0, $pos);
        $instance->shortName = substr($classFqn, $pos + 1);

        return $instance;
    }

    public function getNamespace() 
    {
        return $this->namespace;
    }

    public function getShortName() 
    {
        return $this->shortName;
    }
}
