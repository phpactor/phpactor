<?php

namespace Phpactor\Extension\Debug\Model;

use RuntimeException;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;

final class ConfigInitializer
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    /**
     * @var string
     */
    private $schemaPath;

    /**
     * @var string
     */
    private $configPath;


    public function __construct(string $schemaPath, string $configPath)
    {
        $this->schemaPath = $schemaPath;
        $this->configPath = $configPath;
    }

    public function configPath(): string
    {
        return $this->configPath;
    }

    public function initialize(): string
    {
        if (!file_exists($this->configPath)) {
            if (false === file_put_contents($this->configPath, $this->createConfig())) {
                throw new RuntimeException(sprintf(
                    'Could not write Phpactor config file to "%s"',
                    $this->configPath
                ));
            }
            return self::ACTION_CREATED;
        }

        $config = file_get_contents($this->configPath);
        if (false === $config) {
            throw new RuntimeException(sprintf(
                'Could not read config file "%s"',
                $this->configPath
            ));
        }

        $json = json_decode($config);
        if (null === $json) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON file "%s"',
                $this->configPath
            ));
        }

        $json->{'$schema'} = $this->schemaPath;

        file_put_contents($this->configPath, json_encode($json, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));

        return self::ACTION_UPDATED;
    }

    private function createConfig(): string
    {
        return <<<EOT
            {
                "\$schema": "{$this->schemaPath}"
            }
            EOT
        ;
    }
}
