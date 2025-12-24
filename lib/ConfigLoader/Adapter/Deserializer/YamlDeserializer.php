<?php

namespace Phpactor\ConfigLoader\Adapter\Deserializer;

use Phpactor\ConfigLoader\Core\Deserializer;
use Phpactor\ConfigLoader\Core\Exception\CouldNotDeserialize;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlDeserializer implements Deserializer
{
    public function __construct(private Parser $parser = new Parser())
    {
    }

    public function deserialize(string $contents): array
    {
        try {
            return $this->parser->parse($contents);
        } catch (ParseException $exception) {
            throw new CouldNotDeserialize(sprintf(
                'Could not deserialize YAML, error from parser "%s"',
                $exception->getMessage()
            ), 0, $exception);
        }
    }
}
