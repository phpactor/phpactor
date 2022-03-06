<?php

namespace Phpactor\Extension\ExtensionManager\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class Extensions implements IteratorAggregate, Countable
{
    private $extensions = [];

    public function __construct(array $extensions)
    {
        foreach ($extensions as $extension) {
            $this->add($extension);
        }
    }

    public function merge(Extensions $extensions): Extensions
    {
        return new Extensions(array_merge($this->extensions, $extensions->extensions));
    }

    public function sorted(): Extensions
    {
        $extensions = $this->extensions;
        usort($extensions, function (Extension $one, Extension $two) {
            return $one->name() <=> $two->name();
        });
        return new Extensions($extensions);
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->extensions);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->extensions);
    }

    public function names(): array
    {
        return array_map(function (Extension $extension) {
            return $extension->name();
        }, $this->extensions);
    }

    public function primaries(): Extensions
    {
        return new Extensions(array_filter($this->extensions, function (Extension $extension) {
            return $extension->state()->isPrimary();
        }));
    }

    private function add(Extension $extension): void
    {
        $this->extensions[$extension->name()] = $extension;
    }
}
