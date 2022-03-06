<?php

namespace Phpactor\ClassMover\Domain\Name;

use InvalidArgumentException;

class QualifiedName
{
    protected $parts;

    protected function __construct(array $parts)
    {
        $this->parts = $parts;
    }

    public function __toString()
    {
        return implode('\\', $this->parts);
    }

    public static function root(): QualifiedName
    {
        return new static([]);
    }

    public function isEqualTo(QualifiedName $name)
    {
        return $name->__toString() == $this->__toString();
    }

    public static function fromString(string $string)
    {
        if (empty($string)) {
            throw new InvalidArgumentException(
                'Name cannot be empty'
            );
        }

        $parts = explode('\\', trim($string));

        return new static($parts);
    }

    public function base()
    {
        return reset($this->parts);
    }

    public function parentNamespace(): QualifiedName
    {
        $parts = $this->parts;
        array_pop($parts);

        return new static($parts);
    }

    public function equals(QualifiedName $qualifiedName)
    {
        return $qualifiedName->__toString() == $this->__toString();
    }

    public function head()
    {
        return end($this->parts);
    }

    public function transpose(QualifiedName $name)
    {
        // both fully qualified names? great, nothing to see here.
        if ($this instanceof FullyQualifiedName && $name instanceof FullyQualifiedName) {
            return $name;
        }

        // pretty sure there are some holes in this logic..
        $newParts = [];
        $replaceParts = $name->parts();

        for ($index = 0; $index < count($this->parts); ++$index) {
            $newParts[] = array_pop($replaceParts);
        }

        return new self(array_reverse(array_filter($newParts)));
    }

    public function parts()
    {
        return $this->parts;
    }

    public function isAlone()
    {
        return count($this->parts) === 1;
    }
}
