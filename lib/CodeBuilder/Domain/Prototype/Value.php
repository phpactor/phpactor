<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Value
{
    protected $value;

    protected function __construct($value = null)
    {
        $this->value = $value;
    }

    public static function fromValue($value)
    {
        return new static($value);
    }

    public function value()
    {
        return $this->value;
    }

    public function export()
    {
        if ($this->value === null) {
            return 'null';
        }

        if (is_array($this->value)) {
            return self::renderArray($this->value);
        }

        return var_export($this->value, true);
    }

    /**
     * @param array<mixed> $array
     */
    private static function renderArray(array $array): string
    {
        $parts = [];
        $isList = array_keys($array) === range(0, count($array) - 1);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = self::renderArray($array);
            }
            if (!is_scalar($value)) {
                continue;
            }
            if ($isList) {
                $parts[] = sprintf('%s', json_encode($value));
                continue;
            }
            $parts[] = sprintf('%s => %s', json_encode($key), json_encode($value));
        }

        return sprintf('[%s]', implode(', ', $parts));
    }
}
