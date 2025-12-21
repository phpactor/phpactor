<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\MemberReference;
use Phpactor\Indexer\Model\Record;
use RuntimeException;

class MemberRecord implements HasFileReferences, Record, HasShortName
{
    use HasFileReferencesTrait;
    public const RECORD_TYPE = 'member';
    public const TYPE_METHOD = 'method';
    public const TYPE_CONSTANT = 'constant';
    public const TYPE_PROPERTY = 'property';
    private const ID_DELIMITER = '#';

    /**
     * @var MemberRecord::TYPE_*
     */
    private string $type;

    /**
     * @param MemberRecord::TYPE_* $type
     */
    public function __construct(
        string $type,
        private string $memberName,
        private ?string $containerType = null
    ) {
        if (!in_array($type, [
            self::TYPE_PROPERTY,
            self::TYPE_CONSTANT,
            self::TYPE_METHOD,
        ])) {
            throw new RuntimeException(sprintf(
                'Invalid member type "%s" use one of MemberType::TYPE_*',
                $type
            ));
        }

        $this->type = $type;
    }

    public static function fromMemberReference(MemberReference $memberReference): self
    {
        return new self($memberReference->type(), $memberReference->memberName(), $memberReference->containerType());
    }

    public function recordType(): string
    {
        return self::RECORD_TYPE;
    }

    public function identifier(): string
    {
        return $this->type . self::ID_DELIMITER . $this->memberName;
    }

    public static function isIdentifier(string $identifier): bool
    {
        return count(explode(self::ID_DELIMITER, $identifier)) === 2;
    }

    public static function fromIdentifier(string $identifier): self
    {
        if (!self::isIdentifier($identifier)) {
            throw new RuntimeException(sprintf(
                'Invalid member identifier "%s", must be <type>#<name> e.g. "property#foobar"',
                $identifier
            ));
        }

        $parts = explode(self::ID_DELIMITER, $identifier);
        [$type, $memberName] = $parts;

        /** @phpstan-ignore-next-line */
        return new self($type, $memberName);
    }

    public function memberName(): string
    {
        return $this->memberName;
    }

    public function containerType(): ?string
    {
        return $this->containerType;
    }

    /**
     * @return MemberRecord::TYPE_*
     */
    public function type(): string
    {
        return $this->type;
    }

    public function shortName(): string
    {
        return $this->memberName;
    }
}
