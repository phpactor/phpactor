<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use Phpactor\ConfigLoader\Core\Deserializer;
use Phpactor\ConfigLoader\Core\Deserializers;
use Phpactor\ConfigLoader\Core\Exception\DeserializerNotFound;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class DeserializersTest extends TestCase
{
    use ProphecyTrait;
    
    /**
     * @var ObjectProphecy<Deserializer>
     */
    private ObjectProphecy $deserializer;

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
