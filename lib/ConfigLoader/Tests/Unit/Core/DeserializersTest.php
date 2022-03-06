<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\ConfigLoader\Core\Deserializer;
use Phpactor\ConfigLoader\Core\Deserializers;
use Phpactor\ConfigLoader\Core\Exception\DeserializerNotFound;

class DeserializersTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $deserializer;

    public function setUp(): void
    {
        $this->deserializer = $this->prophesize(Deserializer::class);
    }

    public function testExceptionOnUnregisteredLoader(): void
    {
        $this->expectException(DeserializerNotFound::class);
        $this->expectExceptionMessage('No deserializer registered');
        $deserializers = new Deserializers([
            'xml' => $this->deserializer->reveal(),
            'json' => $this->deserializer->reveal(),
        ]);

        $deserializers->get('asd');
    }
}
