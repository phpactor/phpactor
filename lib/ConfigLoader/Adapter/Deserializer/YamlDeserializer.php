<?php

namespace Phpactor\ConfigLoader\Adapter\Deserializer;

use Phpactor\ConfigLoader\Core\Deserializer;
use Phpactor\ConfigLoader\Core\Exception\CouldNotDeserialize;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlDeserializer implements Deserializer
{
    private Parser $parser;

    public function __construct(?Parser $parser = null)
    {
        $this->parser = $parser ?: new Parser();
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
