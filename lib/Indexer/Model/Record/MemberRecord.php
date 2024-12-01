<?php

namespace Phpactor\Indexer\Model\Record;

use Phpactor\Indexer\Model\MemberReference;
use Phpactor\Indexer\Model\Record;
use RuntimeException;

class MemberRecord implements HasFileReferences, Record, HasShortName
{
    use HasFileReferencesTrait;
    private const ID_DELIMITER = '#';

    public function __construct(
        private MemberRecordType $type,
        private string $memberName,
        private ?string $containerType = null,
    ) {
    }

    public static function fromMemberReference(MemberReference $memberReference): self
    {
        return new self($memberReference->type(), $memberReference->memberName(), $memberReference->containerType());
    }

    public function recordType(): RecordType
    {
        return RecordType::MEMBER;
    }

    public function identifier(): string
    {
        return $this->type->value . self::ID_DELIMITER . $this->memberName;
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

        return new self(MemberRecordType::from($type), $memberName);
    }

    public function memberName(): string
    {
        return $this->memberName;
    }

    public function containerType(): ?string
    {
        return $this->containerType;
    }

    public function type(): MemberRecordType
    {
        return $this->type;
    }

    public function shortName(): string
    {
        return $this->memberName;
    }
}
