<?php

namespace Phpactor\ClassMover\Domain\Model;

use Phpactor\ClassMover\Domain\Name\MemberName;
use InvalidArgumentException;

final class ClassMemberQuery
{
    const TYPE_CONSTANT = 'constant';
    const TYPE_METHOD = 'method';
    const TYPE_PROPERTY = 'property';

    /** @var array<string> */
    private array $validTypes = [
        self::TYPE_CONSTANT,
        self::TYPE_METHOD,
        self::TYPE_PROPERTY
    ];

    private readonly ?string $type;

    private function __construct(
        private readonly ?Class_ $class = null,
        private readonly ?MemberName $memberName = null,
        ?string $type = null
    ) {
        if (null !== $type && false === in_array($type, $this->validTypes)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid member type "%s", valid types: "%s"',
                $type,
                implode('", "', $this->validTypes)
            ));
        }

        $this->type = $type;
    }

    public function __toString(): string
    {
        return (string) $this->class;
    }

    public static function create(): ClassMemberQuery
    {
        return new self();
    }

    public function onlyConstants(): self
    {
        return new self(
            $this->class,
            $this->memberName,
            self::TYPE_CONSTANT
        );
    }

    public function onlyMethods(): self
    {
        return new self(
            $this->class,
            $this->memberName,
            self::TYPE_METHOD
        );
    }

    public function onlyProperties(): self
    {
        return new self(
            $this->class,
            $this->memberName,
            self::TYPE_PROPERTY
        );
    }

    /**
     * If the argument is anything other than a Class_ or string then it will throw an error.
     *
     * @param Class_|string|mixed $className
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
     * If the argument is anything but a MemberName or a string this class will throw an error.
     *
     * @param MemberName|string|mixed $memberName
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

    public function memberName(): ?MemberName
    {
        return $this->memberName;
    }

    public function matchesMemberName(string $memberName): bool
    {
        if (null === $this->memberName) {
            return true;
        }

        return $this->memberName->matches($memberName);
    }

    public function matchesClass(string $className): bool
    {
        if (null === $this->class) {
            return true;
        }

        return $className == (string) $this->class;
    }

    public function class(): ?Class_
    {
        return $this->class;
    }

    public function type(): ?string
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
