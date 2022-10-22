<?php

namespace Prophecy\Prophecy;

/**
 * @template T of object
 * @template-implements ProphecyInterface<T>
 */
class ObjectProphecy implements ProphecyInterface
{
    /**
     * @return T
     */
    public function reveal()
    {
    }
}

