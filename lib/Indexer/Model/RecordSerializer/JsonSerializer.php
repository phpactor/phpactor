<?php

namespace Phpactor\Indexer\Model\RecordSerializer;

use Phpactor\Indexer\Model\Record;
use Phpactor\Indexer\Model\RecordSerializer;

class JsonSerializer implements RecordSerializer
{
    public function serialize(Record $record): string
    {
        $className = get_class($record);
        $data = [];
        $reflection = new \ReflectionObject($record);
        foreach ($reflection->getProperties() as $property) {
            if (!$property->isInitialized($record)) { continue; }
            $data[$property->getName()] = $property->getValue($record);
        }

        return json_encode(
            ['class' => $className, 'data' => $data],
            JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES
        );
    }

    public function deserialize(string $data): ?Record
    {
        if ($data[0] !== '{') {
            dump(unserialize($data));
            return unserialize($data);
        }

        $data = json_decode($data, true);
        if (!is_array($data)) {
            dump($data);
            return null;
        }

        $reflection =new \ReflectionClass($data['class']);
        $object = $reflection->newInstanceWithoutConstructor();
        foreach ($data as $property => $value) {
            $reflection->getProperty($property)->setValue($value, $object);
        }
        dump($object);

        return $object;
    }
}

