<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Adapter\Deserializer;

use Phpactor\ConfigLoader\Adapter\Deserializer\JsonDeserializer;
use Phpactor\ConfigLoader\Core\Exception\CouldNotDeserialize;
use Phpactor\ConfigLoader\Tests\TestCase;

class JsonDeserializerTest extends TestCase
{
    public function testThrowsExceptionIfInvalidJson(): void
    {
        $this->expectException(CouldNotDeserialize::class);
        (new JsonDeserializer())->deserialize('FOo BAR');
    }
}
