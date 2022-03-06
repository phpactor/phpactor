<?php

namespace Phpactor\ConfigLoader\Adapter\Deserializer;

use Phpactor\ConfigLoader\Core\Deserializer;
use Phpactor\ConfigLoader\Core\Exception\CouldNotDeserialize;

class JsonDeserializer implements Deserializer
{
    public function deserialize(string $contents): array
    {
        $decoded = json_decode($contents, true);

        if (null === $decoded) {
            throw new CouldNotDeserialize(sprintf(
                'Could not decode JSON: "%s"',
                json_last_error_msg()
            ));
        }

        return $decoded;
    }
}
