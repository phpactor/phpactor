<?php

namespace Phpactor\Extension\Core\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Core\Model\JsonSchemaBuilder;
use Phpactor\MapResolver\Resolver;

class JsonSchemaBuilderTest extends TestCase
{
    public function testBuild(): void
    {
        $extensions = [];
        $extensions[] = get_class($this->createExtension1());

        $schema = (new JsonSchemaBuilder('test', $extensions))->dump();
        file_put_contents('foo', $schema);
        self::assertEquals(<<<'EOT'
            {
                "$schema": "https:\/\/json-schema.org\/draft-07\/schema",
                "title": "test",
                "type": "object",
                "properties": {
                    "$schema": {
                        "description": "JSON schema location",
                        "type": [
                            "string",
                            "null"
                        ]
                    },
                    "bar.foo": {
                        "description": "This does something",
                        "type": [
                            "string"
                        ],
                        "default": 1234
                    },
                    "foo.bar": {
                        "description": null,
                        "type": [
                            "string"
                        ],
                        "default": "bar"
                    }
                }
            }
            EOT
, $schema);
    }

    private function createExtension1(): Extension
    {
        return new class implements Extension {
            public function configure(Resolver $resolver): void
            {
                $resolver->setDefaults([
                    'bar.foo' => 1234,
                    'foo.bar' => 'bar',
                ]);
                $resolver->setRequired([
                    'bar.foo',
                ]);
                $resolver->setTypes([
                    'bar.foo' => 'string',
                    'foo.bar' => 'string',
                ]);
                $resolver->setDescriptions([
                    'bar.foo' => 'This does something',
                ]);
            }

            public function load(ContainerBuilder $builder): void
            {
            }
        };
    }
}
