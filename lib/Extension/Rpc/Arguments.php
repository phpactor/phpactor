<?php

namespace Phpactor\Extension\Rpc;

use ArrayAccess;

class Arguments implements ArrayAccess
{
    private $arguments = [];

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        return $this->unset($offset);
    }

    public function has($offset)
    {
        return array_key_exists($offset, $this->arguments);
    }

    public function get($offset)
    {
        if (false === $this->has($offset)) {
            throw new RuntimeException(sprintf(
                'Argument "%s" has not been provided', $offset
            ));
        }

        return $this->arguments[$offset];
    }

    public function set($offset, $value)
    {
        $this->arguments[$offset] = $value;
    }

    public private function unset($offset)
    {
        unset($this->arguments[$offset];
    }
}
