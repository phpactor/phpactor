<?php

namespace Phpactor\Tests\Unit\Rpc\Response;

use PHPUnit\Framework\TestCase;
use Phpactor\Rpc\Response\ErrorResponse;

class ErrorResponseTest extends TestCase
{
    public function testFromException()
    {
        $exception = new \Exception('Hello');
        $response = ErrorResponse::fromException($exception);

        $this->assertEquals('Hello', $response->message());
    }

    public function testFromExceptionWithPrevious()
    {
        $exception1 = new \Exception('One');
        $exception2 = new \Exception('Two', null, $exception1);
        $exception3 = new \Exception('Three', null, $exception2);
        $response = ErrorResponse::fromException($exception3);

        $this->assertEquals('Three', $response->message());
        $this->assertContains('One', $response->details());
        $this->assertContains('Two', $response->details());
        $this->assertContains('Three', $response->details());
    }
}
