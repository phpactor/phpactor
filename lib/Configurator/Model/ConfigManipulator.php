<?php

namespace Phpactor\Configurator\Model;

use RuntimeException;
use stdClass;
use function file_get_contents;
use function file_put_contents;
use function json_decode;
use function json_encode;

/**
 * @deprecated This class is specifically for Phpactor configuration, we should
 * generalize this.
 */
final class ConfigManipulator
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';

    public function __construct(private string $schemaPath, private string $configPath)
    {
    }

    public function configPath(): string
    {
        return $this->configPath;
    }

    public function initialize(): string
    {
        $json = $this->openConfig();
        $json->{'$schema'} = $this->schemaPath;
        $this->writeConfig($json);

        return self::ACTION_UPDATED;
    }

    /**
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $json = $this->openConfig();
        $json->{$key} = $value;
        $this->writeConfig($json);
    }

    public function delete(string $key): void
    {
        $json = $this->openConfig();
        unset($json->{$key});
        $this->writeConfig($json);
    }

    private function createConfig(): string
    {
        $value = [ '$schema' => $this->schemaPath ];
        return json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
    }

    private function openConfig(): stdClass
    {
        if (!file_exists($this->configPath)) {
            if (false === file_put_contents($this->configPath, $this->createConfig())) {
                throw new RuntimeException(sprintf(
                    'Could not write Phpactor config file to "%s"',
                    $this->configPath
                ));
            }
        }

        $config = file_get_contents($this->configPath);
        if (false === $config) {
            throw new RuntimeException(sprintf(
                'Could not read config file "%s"',
                $this->configPath
            ));
        }

        $json = json_decode($config);
        if (!$json instanceof stdClass) {
            throw new RuntimeException(sprintf(
                'Could not decode JSON file "%s"',
                $this->configPath
            ));
        }

        return $json;
    }

    /**
     * @param mixed $value
     */
    private function writeConfig($value): void
    {
        file_put_contents($this->configPath, json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}
