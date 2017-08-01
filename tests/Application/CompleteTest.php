<?php

namespace Phpactor\Tests\Application;

use PHPUnit\Framework\TestCase;
use Phpactor\Application\Complete;

class CompleteTest extends TestCase
{
    public function setUp()
    {
        $this->complete = new Complete();
    }

    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $code, int $offset, array $expectedCompletions)
    {
    }
}
