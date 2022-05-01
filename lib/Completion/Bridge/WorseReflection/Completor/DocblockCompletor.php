<?php

namespace Phpactor\Completion\Bridge\WorseReflection\Completor;

use Generator;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\ReferenceFinder\NameSearcher;
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
    const TAGS_WITH_TYPE_ARG = [
        '@param',
        '@var',
        '@return',
        '@method',
        '@property',
        '@implements',
        '@extends',
    ];

    private NameSearcher $nameSearcher;

    public function __construct(NameSearcher $nameSearcher)
    {
        $this->nameSearcher = $nameSearcher;
    }

    public function complete(TextDocument $source, ByteOffset $byteOffset): Generator
    {
        [$tag, $rest] = $this->extractTag($source, $byteOffset);

        if (null === $tag) {
            return false;
        }

        $tag = '@' . $tag;

        if (in_array($tag, self::SUPPORTED_TAGS)) {
            yield from $this->completeType($tag, $rest);
            return false;
        }

        foreach (self::SUPPORTED_TAGS as $supportedTag) {
            if (0 === strpos($supportedTag, $tag)) {
                yield Suggestion::createWithOptions(
                    $supportedTag,
                    [
                        'type' => Suggestion::TYPE_KEYWORD,
                    ]
                );
            }
        }
        return true;
    }

    /**
     * @return array{string|null,string}
     */
    private function extractTag(TextDocument $source, ByteOffset $byteOffset): array
    {
        $source = substr($source->__toString(), 0, $byteOffset->toInt());
        $line = LineAtOffset::lineAtByteOffset($source, $byteOffset);

        if (!preg_match('{^\s*/?\*{1,2}\s*@([a-z-]*)\s*([^\s]*)}', $line, $matches)) {
            return [null, ''];
        }

        return [$matches[1], $matches[2]];
    }

    private function completeType(string $tag, string $rest): Generator
    {
        if (in_array($tag, self::TAGS_WITH_TYPE_ARG)) {
            yield from $this->nameResults($rest);
        }
    }

    private function nameResults(string $rest): Generator
    {
        foreach ($this->nameSearcher->search($rest) as $result) {
            if (!$result->type()->isClass()) {
                continue;
            }

            yield Suggestion::createWithOptions($result->name()->head(), [
                'name_import' => $result->name()->__toString(),
                'type' => Suggestion::TYPE_CLASS,
            ]);
        }
    }
}
