<?php

declare(strict_types=1);

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\StatementNode;
use Phpactor\Completion\Bridge\TolerantParser\CompletionContext;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class KeywordCompletor implements TolerantCompletor
{
    private const EXPRESSIONS = [
        'match' => " (\$1) {\$0\n}",
        'throw' => ' $1',
    ];
    private const MAGIC_METHODS = [
        '__construct' => "(\$1)\n{\$0\n}",
        '__call' => "(string \\\$\${1:name}, array \\\$\${2:arguments}): \${3:mixed}\n{\$0\n}",
        '__callStatic' => "(string \\\$\${1:name}, array \\\$\${2:arguments}): \${3:mixed}\n{\$0\n}",
        '__clone' => "(): void\n{\$0\n}",
        '__debugInfo' => "(): array\n{\$0\n}",
        '__destruct' => "(): void\n{\$0\n}",
        '__get' => "(string \\\$\${1:name}): \${3:mixed}\n{\$0\n}",
        '__invoke' => "(\$1): \${2:mixed}\n{\$0\n}",
        '__isset' => "(string \\\$\${1:name}): bool\n{\$0\n}",
        '__serialize' => "(): array\n{\$0\n}",
        '__set' => "(string \\\$\${1:name}, mixed \\\$\${2:value}): void\n{\$0\n}",
        '__set_state' => "(array \\\$\${1:properties}): object\n{\$0\n}",
        '__sleep' => "(): array\n{\$0\n}",
        '__toString' => "(): string\n{\$0\n}",
        '__unserialize' => "(array \\\$\${1:data}): void\n{\$0\n}",
        '__unset' => "(string \\\$\${1:name}): void\n{\$0\n}",
        '__wakeup' => "(): void\n{\$0\n}",
    ];

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        if (CompletionContext::promotedPropertyVisibility($node)) {
            yield from $this->keywords(['private ', 'public ', 'protected ']);
            return true;
        }
        if (CompletionContext::classClause($node, $offset)) {
            yield from $this->keywords(['implements ', 'extends ']);
            return true;
        }
        if (CompletionContext::declaration($node, $offset)) {
            yield from $this->keywords(['class ', 'enum ', 'trait ', 'function ', 'interface ']);
            return true;
        }

        if (CompletionContext::conditionInfix($node)) {
            yield from $this->keywords(['instanceof ']);
            return true;
        }

        if (CompletionContext::methodName($node)) {
            yield from $this->methods();
            return true;
        }

        if (CompletionContext::attribute($node)) {
            return true;
        }

        if (CompletionContext::expression($node)) {
            yield from $this->expressions();
            return true;
        }

        if (
            !$node instanceof MethodDeclaration
                && CompletionContext::classMembersBody($node->parent)
                && !$node->parent instanceof StatementNode
        ) {
            yield from $this->keywords([
                'function ',
                'const ',
            ]);
            return true;
        }

        if (CompletionContext::classMembersBody($node)) {
            yield from $this->keywords(['private ', 'protected ', 'public ']);
            return true;
        }

        return true;
    }

    /**
     * @return Generator<Suggestion>
     */
    private function expressions(): Generator
    {
        foreach (self::EXPRESSIONS as $name => $snippet) {
            yield Suggestion::createWithOptions($name . ' ', [
                'type' => Suggestion::TYPE_KEYWORD,
                'priority' => -255,
                'snippet' => $name . $snippet,
            ]);
        }
    }

    /**
     * @return Generator<Suggestion>
     */
    private function methods(): Generator
    {
        foreach (self::MAGIC_METHODS as $name => $snippet) {
            yield Suggestion::createWithOptions($name . '(', [
                'type' => Suggestion::TYPE_METHOD,
                'priority' => match ($name) {
                    '__construct' => -255,
                    default => 1,
                },
                'snippet' => $name . $snippet,
            ]);
        }
    }

    /**
     * @return Generator<Suggestion>
     * @param string[] $keywords
     */
    private function keywords(array $keywords): Generator
    {
        foreach ($keywords as $keyword) {
            yield Suggestion::createWithOptions($keyword, [
                'type' => Suggestion::TYPE_KEYWORD,
                'priority' => 1,
            ]);
        }
    }
}
