<?php

declare(strict_types=1);

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\TokenStringMaps;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class KeywordCompletor implements TolerantCompletor
{
    public const MEMBER_ACCESS = 'member_access';
    public const CLASS_MEMBERS = 'class_members';
    public const STRING_LITERAL = 'string_literal';
    public const VARIABLE = 'variable';
    public const AFTER_ANONYMOUS_FUNC_PARAMS = 'after_anonymous_func_params';

    public const SPECIAL_SCOPES = [
        self::MEMBER_ACCESS => [ ],
        self::STRING_LITERAL => [ ],
        self::VARIABLE => [ ],
        // https://github.com/php/php-langspec/blob/master/spec/10-expressions.md#anonymous-function-creation
        self::AFTER_ANONYMOUS_FUNC_PARAMS => [
            'use'
        ],
        // https://github.com/php/php-langspec/blob/master/spec/14-classes.md#grammar-class-member-declaration
        self::CLASS_MEMBERS => [
            // visibility
            'public',
            'private',
            'protected',
            // scope
            'static',
            // property
            'var',
            // const
            'const',
            // method
            'function',
            // trait use
            'use',
        ],
    ];

    /**
     * @var string[]
     */
    private $allKeywords = null;
    
    public function initKeywords(): void
    {
        if ($this->allKeywords === null) {
            $this->allKeywords = array_merge(
                array_keys(TokenStringMaps::RESERVED_WORDS),
                array_keys(TokenStringMaps::KEYWORDS)
            );
        }
    }
    /**
    * {@inheritDoc}
    */
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $this->initKeywords();

        $scopeName = $this->getScopeName($node, $offset);

        $keywords =
            (isset(self::SPECIAL_SCOPES[$scopeName])) ?
                self::SPECIAL_SCOPES[$scopeName] :
                $this->allKeywords;

        foreach ($keywords as $keyword) {
            yield Suggestion::createWithOptions(
                $keyword,
                [
                    'type' => Suggestion::TYPE_KEYWORD
                ]
            );
        }
        return true;
    }

    private function getScopeName(Node $node, ByteOffset $offset): ?string
    {
        if (
            ($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression)
            && ($node->memberName->getEndPosition() == $offset->toInt())
        ) {
            return self::MEMBER_ACCESS;
        }
        
        if ($node instanceof ClassMembersNode) {
            return self::CLASS_MEMBERS;
        }
        
        if ($node instanceof StringLiteral) {
            return self::STRING_LITERAL;
        }
        
        if (
            $node instanceof Variable
            && ($node->name->getEndPosition() == $offset->toInt())
        ) {
            return self::VARIABLE;
        }
        
        if (
            $node instanceof CompoundStatementNode
            && $node->getParent() instanceof AnonymousFunctionCreationExpression
            && $node->getParent()->closeParen->getEndPosition() == ($offset->toInt() - 1)
            && (
                $node->getParent()->colonToken == null
                || $node->getParent()->colonToken->getStartPosition() > $offset->toInt()
            )
        ) {
            return self::AFTER_ANONYMOUS_FUNC_PARAMS;
        }

        return null;
    }
}
