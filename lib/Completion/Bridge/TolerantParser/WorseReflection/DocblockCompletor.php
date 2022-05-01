<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\TypeSuggestionProvider;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\Util\LineAtOffset;

class DocblockCompletor implements TolerantCompletor
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
    const TAGS_WITH_VAR = [
        '@param',
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

    private TypeSuggestionProvider $typeSuggestionProvider;

    private Parser $parser;

    public function __construct(TypeSuggestionProvider $typeSuggestionProvider, Parser $parser)
    {
        $this->typeSuggestionProvider = $typeSuggestionProvider;
        $this->parser = $parser;
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $byteOffset): Generator
    {
        // we re-parse the document because the above node is for the truncated
        // doc, which will often (if not always) result in a SourceFileNode
        // with no namespace context
        $node = $this->parser->parseSourceFile($source->__toString())->getDescendantNodeAtPosition($byteOffset->toInt());
        [$tag, $type, $var] = $this->extractTag($source, $byteOffset);

        if (null === $tag) {
            return false;
        }

        $tag = '@' . $tag;

        if ($var) {
            yield from $this->varCompletion($node, $byteOffset, $tag, $var);
            return;
        }

        if (in_array($tag, self::SUPPORTED_TAGS)) {
            yield from $this->completeType($node, $tag, $type);
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
     * @return array{string|null,string,string|null}
     */
    private function extractTag(TextDocument $source, ByteOffset $byteOffset): array
    {
        $source = substr($source->__toString(), 0, $byteOffset->toInt());
        $line = LineAtOffset::lineAtByteOffset($source, $byteOffset);

        if (!preg_match('{/?\*{1,2}\s*@([a-z-]*)\s*([^\s]*)\s*(\$[^\s]*)?}', $line, $matches)) {
            return [null, '', ''];
        }

        return [$matches[1], $matches[2], $matches[3] ?? null];
    }

    private function completeType(Node $node, string $tag, string $search): Generator
    {
        yield from $this->typeSuggestionProvider->provide($node, $search);
    }

    private function varCompletion(Node $node, ByteOffset $offset, string $tag, string $var): Generator
    {
        if (!in_array($tag, self::TAGS_WITH_VAR)) {
            return;
        }
        foreach ($node->getDescendantNodes() as $node) {
            if ($node->getStartPosition() > $offset->toInt()) {
                break;
            }
        }
        if (!$node instanceof FunctionDeclaration && !$node instanceof MethodDeclaration) {
            return;
        }

        /** @phpstan-ignore-next-line */
        if (!$node->parameters) {
            return;
        }

        foreach ($node->parameters->getElements() as $parameter) {
            if (!$parameter instanceof Parameter) {
                continue;
            }
            yield Suggestion::createWithOptions(
                '$' . $parameter->getName(),
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                ]
            );
        }
    }
}
