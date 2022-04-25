<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;

class StringLiteralType extends StringType implements Literal, Generalizable, Concatable
{
    use LiteralTrait;

    public string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return sprintf('"%s"', $this->value);
    }
    
    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    public function generalize(): Type
    {
        return new StringType();
    }

    public function concat(Type $right): Type
    {
        if ($right instanceof StringLiteralType) {
            return new self(sprintf('%s%s', $this->value, (string)$right->value()));
        }
        return new StringType();
    }

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof StringLiteralType) {
            return Trinary::fromBoolean($type->equals($this));
        }

        if ($type instanceof StringType) {
            return Trinary::maybe();
        }

        return parent::accepts($type);
    }
}
