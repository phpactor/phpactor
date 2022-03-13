<?php

namespace Phpactor\ClassMover\Domain\Model;

use Phpactor\ClassMover\Domain\Name\MemberName;
use InvalidArgumentException;

final class ClassMemberQuery
{
    const TYPE_CONSTANT = 'constant';
    const TYPE_METHOD = 'method';
    const TYPE_PROPERTY = 'property';

    private $validTypes = [
        self::TYPE_CONSTANT,
        self::TYPE_METHOD,
        self::TYPE_PROPERTY
    ];

    private ?Class_ $class;

    private ?MemberName $memberName;

    private ?string $type;

    private function __construct(Class_ $class = null, MemberName $memberName = null, string $type = null)
    {
        $this->class = $class;
        $this->memberName = $memberName;

        if (null !== $type && false === in_array($type, $this->validTypes)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid member type "%s", valid types: "%s"',
                $type,
                implode('", "', $this->validTypes)
            ));
        }

        $this->type = $type;
    }

    public function __toString()
    {
        return $this->class;
    }

    public static function create(): ClassMemberQuery
    {
        return new self();
    }

    public function onlyConstants()
    {
        return new self(
            $this->class,
            $this->memberName,
            self::TYPE_CONSTANT
        );
    }

    public function onlyMethods()
    {
        return new self(
            $this->class,
            $this->memberName,
            self::TYPE_METHOD
        );
    }

    public function onlyProperties()
    {
        return new self(
            $this->class,
            $this->memberName,
            self::TYPE_PROPERTY
        );
    }

    /**
     * @var Class_|string
     */
    public function withClass($className): ClassMemberQuery
    {
        if (false === is_string($className) && false === $className instanceof Class_) {
            throw new InvalidArgumentException(sprintf(
                'Class must be either a string or an instanceof Class_, got: "%s"',
                gettype($className)
            ));
        }

        return new self(
            is_string($className) ? Class_::fromString($className) : $className,
            $this->memberName,
            $this->type
        );
    }

    /**
     * @var MemberName|string
     */
    public function withMember($memberName): ClassMemberQuery
    {
        if (false === is_string($memberName) && false === $memberName instanceof MemberName) {
            throw new InvalidArgumentException(sprintf(
                'Member must be either a string or an instanceof MemberName, got: "%s"',
                gettype($memberName)
            ));
        }

        return new self(
            $this->class,
            is_string($memberName) ? MemberName::fromString($memberName) : $memberName,
            $this->type
        );
    }

    public function withType(string $memberType): ClassMemberQuery
    {
        return new self(
            $this->class,
            $this->memberName,
            $memberType
        );
    }

    public function memberName(): MemberName
    {
        return $this->memberName;
    }

    public function matchesMemberName(string $memberName)
    {
        if (null === $this->memberName) {
            return true;
        }

        return $this->memberName->matches($memberName);
    }

    public function matchesClass(string $className)
    {
        if (null === $this->class) {
            return true;
        }

        return $className == (string) $this->class;
    }

    public function class(): Class_
    {
        return $this->class;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function hasType(): bool
    {
        return null !== $this->type;
    }

    public function hasClass(): bool
    {
        return null !== $this->class;
    }

    public function hasMember(): bool
    {
        return null !== $this->memberName;
    }
}
