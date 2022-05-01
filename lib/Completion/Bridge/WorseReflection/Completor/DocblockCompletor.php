<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\Util\LineAtOffset;

class DocblockCompletor implements Completor
{
    const SUPPORTED_TAGS = [
        '@property',
        '@var',
        '@param',
        '@return',
        '@method',
        '@deprecated',
        '@extends',
        '@implements',
        '@template',
        '@template-extends',
    ];

    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        $tag = $this->extractTag($source, $byteOffset);

        if (null === $tag) {
            return;
        }

        if (in_array($tag, self::SUPPORTED_TAGS)) {
            throw new \Exception('todo');
        }

        foreach (self::SUPPORTED_TAGS as $supportedTag) {
            if (0 === strpos($supportedTag, '@' . $tag)) {
                yield Suggestion::createWithOptions(
                    $supportedTag,
                    [
                        'type' => Suggestion::TYPE_KEYWORD,
                    ]
                );
            }
        }

    }

    private function extractTag(TextDocument $source, ByteOffset $byteOffset): ?string
    {
        $source = substr($source->__toString(), 0, $byteOffset->toInt());
        $line = LineAtOffset::lineAtByteOffset($source, $byteOffset);
        if (!preg_match('{^\s*/?\*{1,2}\s*@([a-z-]*)(.*)}', $line, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
