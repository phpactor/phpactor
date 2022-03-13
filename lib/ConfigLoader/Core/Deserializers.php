<?php

namespace Phpactor\ConfigLoader\Core;

use Phpactor\ConfigLoader\Core\Exception\DeserializerNotFound;

class Deserializers
{
    private array $deserializerMap = [];

    public function __construct(array $deserializerMap)
    {
        foreach ($deserializerMap as $deserializerExtension => $deserializer) {
            $this->add($deserializerExtension, $deserializer);
        }
    }

    public function get(string $extension): Deserializer
    {
        if (!isset($this->deserializerMap[$extension])) {
            throw new DeserializerNotFound(sprintf(
                'No deserializer registered for extension "%s", deserializers available for: "%s"',
                $extension,
                implode('", "', array_keys($this->deserializerMap))
            ));
        }

        return $this->deserializerMap[$extension];
    }

    private function add($deserializerExtension, Deserializer $deserializer): void
    {
        $this->deserializerMap[$deserializerExtension] = $deserializer;
    }
}
