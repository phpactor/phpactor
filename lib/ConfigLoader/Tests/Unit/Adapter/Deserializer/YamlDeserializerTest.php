<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Adapter\Deserializer;

use Phpactor\ConfigLoader\Adapter\Deserializer\YamlDeserializer;
use Phpactor\ConfigLoader\Core\Exception\CouldNotDeserialize;
use Phpactor\ConfigLoader\Tests\TestCase;

class YamlDeserializerTest extends TestCase
{
    public function testExceptionOnInvalid(): void
    {
        $this->expectException(CouldNotDeserialize::class);
        (new YamlDeserializer())->deserialize(
            <<<'EOT'
                asd 
                 \t 
                a
                 1235
                     123
                EOT
        );
    }

    public function testDeserialize(): void
    {
        $config = (new YamlDeserializer())->deserialize(
            <<<'EOT'
                one:
                   two: three
                EOT
        );

        $this->assertEquals([
            'one' => [
                'two' => 'three',
            ]
        ], $config);
    }
}
