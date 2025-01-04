<?php

namespace Phpactor\Extension\Core\Console\Dumper;

use InvalidArgumentException;

final class DumperRegistry
{
    private $dumpers = [];

    public function __construct(array $dumpers, private string $default)
    {
        foreach ($dumpers as $name => $dumper) {
            $this->add($name, $dumper);
        }
    }

    public function get(?string $name = null): Dumper
    {
        $name = $name ?: $this->default;
        if (!isset($this->dumpers[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Unknown dumper "%s", known dumpers: "%s"',
                $name,
                implode('", "', array_keys($this->dumpers))
            ));
        }

        return $this->dumpers[$name];
    }

    private function add(string $name, Dumper $dumper): void
    {
        $this->dumpers[$name] = $dumper;
    }
}
