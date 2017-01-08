<?php

namespace Phpactor\Tests\Functional\Generation\classes;

interface Example1Interface
{
    public function interfaceMethodOne(\stdClass $foobar = null);

    public function interfaceMethodTwo(int $foobar);
}
