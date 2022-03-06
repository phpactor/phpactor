<?php

namespace Phpactor\CodeTransform\Domain;

use Phpactor\Name\Name;
use Phpactor\TextDocument\ByteOffset;
use RuntimeException;

final class NameWithByteOffset
{
    public const TYPE_CLASS = 'class';
    public const TYPE_FUNCTION = 'function';

    private const VALID_TYPES = [
        self::TYPE_CLASS,
        self::TYPE_FUNCTION
    ];

    /**
     * @var Name
     */
    private $name;
    /**
     * @var ByteOffset
     */
    private $byteOffset;

    /**
     * @var string
     */
    private $type;

    public function __construct(Name $name, ByteOffset $byteOffset, string $type = self::TYPE_CLASS)
    {
        if (!in_array($type, self::VALID_TYPES)) {
            throw new RuntimeException(sprintf(
                'Invalid type "%s", valid types "%s"',
                $type,
                implode('", "', self::VALID_TYPES)
            ));
        }

        $this->name = $name;
        $this->byteOffset = $byteOffset;
        $this->type = $type;
    }

    public function byteOffset(): ByteOffset
    {
        return $this->byteOffset;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function type(): string
    {
        return $this->type;
    }
}
