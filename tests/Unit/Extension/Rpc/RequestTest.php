<?php

namespace Phpactor\Tests\Unit\Extension\Rpc;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Rpc\Request;
use InvalidArgumentException;

class RequestTest extends TestCase
{
    public function testCanBeCreatedFromArrayAndThrowsExceptionIfParametersAreMissing()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing "action" key');
        Request::fromArray([]);
    }

    public function testCanBeCreatedFromArrayParametersAreOptional()
    {
        $request = Request::fromArray([
            'action' => 'foobar',
        ]);
        $result = $request->toArray();

        $this->assertEquals([
            'action' => 'foobar',
            'parameters' => []
        ], $result);
    }

    public function testThrowsExceptionIfInvalidKeysGiven()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request keys "foobar"');

        Request::fromArray([
            'action' => 'fo',
            'foobar' => 'foobar',
        ]);
    }
}
