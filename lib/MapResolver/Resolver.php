<?php

namespace Phpactor\MapResolver;

use Phpactor\MapResolver\Resolver;

class Resolver
{
    /**
     * @var array
     */
    private $required = [];

    /**
     * @var array
     */
    private $defaults = [];

    /**
     * @var array
     */
    private $types = [];

    public function setRequired(array $fields)
    {
        $this->required = $fields;
    }

    public function setDefaults(array $defaults)
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }

    public function setTypes(array $typeMap)
    {
        $this->types = $typeMap;
    }

    public function resolve(array $config)
    {
        $allowedKeys = array_merge(array_keys($this->defaults), $this->required);

        if ($diff = array_diff(array_keys($config), $allowedKeys)) {
            throw new InvalidConfig(sprintf(
                'Keys "%s" are not known, known keys: "%s"',
                implode('", "', ($diff)),
                implode('", "', $allowedKeys)
            ));
        }

        $config = array_merge($this->defaults, $config);

        if ($diff = array_diff($this->required, array_keys($config))) {
            throw new InvalidConfig(sprintf(
                'Key(s) "%s" are required',
                implode('", "', $diff)
            ));
        }

        foreach ($config as $key => $value) {
            if (isset($this->types[$key])) {
                $valid = true;
                $type = null;

                if (is_object($value)) {
                    $type = get_class($value);
                    $valid = $value instanceof $this->types[$key];
                }

                if (false === is_object($value)) {
                    $type = gettype($value);
                    $valid = $this->types[$key] === gettype($value);
                }

                if (false === $valid) {
                    throw new InvalidConfig(sprintf(
                        'Type for "%s" expected to be "%s", got "%s"',
                        $key,
                        $this->types[$key],
                        $type
                    ));
                }
            }
        }

        return $config;
    }

    public function merge(Resolver $schema): Resolver
    {
        foreach ($schema->required as $required) {
            $this->required[] = $required;
        }

        foreach ($schema->defaults as $key => $value) {
            $this->defaults[$key] = $value;
        }

        foreach ($schema->types as $key => $types) {
            $this->types[$key] = $types;
        }

        return $this;
    }
}
