<?php

namespace Phpactor\ClassMover\Domain\Reference;

use Phpactor\ClassMover\Domain\Name\MemberName;
use Phpactor\ClassMover\Domain\Model\Class_;

class MemberReference
{
    /**
     * @var MemberName
     */
    private $method;

    /**
     * @var Position
     */
    private $position;

    /**
     * @var Class_
     */
    private $class;

    private function __construct(MemberName $method, Position $position, Class_ $class = null)
    {
        $this->method = $method;
        $this->position = $position;
        $this->class = $class;
    }

    public function __toString()
    {
        return sprintf(
            '[%s:%s] %s',
            $this->position->start(),
            $this->position->end(),
            (string) $this->method
        );
    }

    public static function fromMemberNameAndPosition(MemberName $method, Position $position): MemberReference
    {
        return new self($method, $position);
    }

    public static function fromMemberNamePositionAndClass(MemberName $method, Position $position, Class_ $class): MemberReference
    {
        return new self($method, $position, $class);
    }

    public function methodName(): MemberName
    {
        return $this->method;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function hasClass(): bool
    {
        return null !== $this->class;
    }

    public function withClass(Class_ $class)
    {
        return new self($this->method, $this->position, $class);
    }

    public function class(): Class_
    {
        return $this->class;
    }
}
