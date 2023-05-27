<?php

namespace Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\ArrayKeyValueList;
use Phpactor\DocblockParser\Ast\ArrayKeyValueNode;
use Phpactor\DocblockParser\Ast\ConditionalNode;
use Phpactor\DocblockParser\Ast\Tag\DeprecatedTag;
use Phpactor\DocblockParser\Ast\Docblock;
use Phpactor\DocblockParser\Ast\Tag\ExtendsTag;
use Phpactor\DocblockParser\Ast\Tag\ImplementsTag;
use Phpactor\DocblockParser\Ast\Tag\MethodTag;
use Phpactor\DocblockParser\Ast\Tag\MixinTag;
use Phpactor\DocblockParser\Ast\ParameterList;
use Phpactor\DocblockParser\Ast\Tag\ParameterTag;
use Phpactor\DocblockParser\Ast\Tag\PropertyTag;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\DocblockParser\Ast\Tag\TemplateTag;
use Phpactor\DocblockParser\Ast\TextNode;
use Phpactor\DocblockParser\Ast\TypeList;
use Phpactor\DocblockParser\Ast\Type\ArrayNode;
use Phpactor\DocblockParser\Ast\Type\ArrayShapeNode;
use Phpactor\DocblockParser\Ast\Type\CallableNode;
use Phpactor\DocblockParser\Ast\Type\ClassNode;
use Phpactor\DocblockParser\Ast\Node;
use Phpactor\DocblockParser\Ast\Tag\ParamTag;
use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\Type\ConstantNode;
use Phpactor\DocblockParser\Ast\Type\GenericNode;
use Phpactor\DocblockParser\Ast\Type\IntersectionNode;
use Phpactor\DocblockParser\Ast\Type\ListBracketsNode;
use Phpactor\DocblockParser\Ast\Type\ListNode;
use Phpactor\DocblockParser\Ast\Type\LiteralFloatNode;
use Phpactor\DocblockParser\Ast\Type\LiteralIntegerNode;
use Phpactor\DocblockParser\Ast\Type\LiteralStringNode;
use Phpactor\DocblockParser\Ast\Type\NullNode;
use Phpactor\DocblockParser\Ast\Type\NullableNode;
use Phpactor\DocblockParser\Ast\Type\ParenthesizedType;
use Phpactor\DocblockParser\Ast\Type\ScalarNode;
use Phpactor\DocblockParser\Ast\Type\ThisNode;
use Phpactor\DocblockParser\Ast\Type\UnionNode;
use Phpactor\DocblockParser\Ast\Type\UnsupportedNode;
use Phpactor\DocblockParser\Ast\UnknownTag;
use Phpactor\DocblockParser\Ast\ValueNode;
use Phpactor\DocblockParser\Ast\Value\NullValue;
use Phpactor\DocblockParser\Ast\Tag\VarTag;
use Phpactor\DocblockParser\Ast\Tag\ThrowsTag;
use Phpactor\DocblockParser\Ast\VariableNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\Tokens;

final class Parser
{
    /**
     * TODO Callable is not a scalar.
     */
    private const SCALAR_TYPES = [
        'int', 'float', 'bool', 'class-string', 'string', 'mixed', 'callable', 'false'
    ];

    private Tokens $tokens;

    public function parse(Tokens $tokens): Node
    {
        $children = [];
        $this->tokens = $tokens;

        while ($tokens->hasCurrent()) {
            /** @phpstan-ignore-next-line Above ensures it is not null */
            if ($tokens->current->type === Token::T_TAG) {
                $children[] = $this->parseTag();
                continue;
            }
            $children[] = $tokens->chomp();
        }

        if (count($children) === 1) {
            $node = reset($children);
            if ($node instanceof Node) {
                return $node;
            }
        }

        /** @phpstan-ignore-next-line */
        return new Docblock($children);
    }

    private function parseTag(): TagNode
    {
        $token = $this->tokens->current;
        $value = str_replace(['@psalm-', '@phpstan-'], '@', $token->value);
        return match ($value) {
            '@param' => $this->parseParam(),
            '@var' => $this->parseVar(),
            '@throws' => $this->parseThrows(),
            '@deprecated' => $this->parseDeprecated(),
            '@method' => $this->parseMethod(),
            '@property', '@property-read' => $this->parseProperty(),
            '@mixin' => $this->parseMixin(),
            '@return' => $this->parseReturn(),
            '@template' => $this->parseTemplate(),
            '@template-covariant' => $this->parseTemplate(),
            '@extends', '@template-extends' => $this->parseExtends(),
            '@implements', '@template-implements' => $this->parseImplements(),
            default => new UnknownTag($this->tokens->chomp()),
        };
    }

    private function parseParam(): ParamTag
    {
        $type = $variable = $textNode = null;
        $tag = $this->tokens->chomp(Token::T_TAG);

        if ($this->ifType()) {
            $type = $this->parseTypes();
        }

        if ($this->tokens->ifNextIs(Token::T_VARIABLE)) {
            $variable = $this->parseVariable();
        }

        return new ParamTag($tag, $type, $variable, $this->parseText());
    }

    private function parseVar(): VarTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $type = $variable = null;
        if ($this->ifType()) {
            $type = $this->parseTypes();
        }
        if ($this->tokens->ifNextIs(Token::T_VARIABLE)) {
            $variable = $this->parseVariable();
        }

        return new VarTag($tag, $type, $variable);
    }

    private function parseThrows(): ThrowsTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $type = null;

        if ($this->tokens->if(Token::T_LABEL)) {
            $type = $this->parseTypes();
        }

        return new ThrowsTag($tag, $type);
    }

    private function parseMethod(): MethodTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $type = $name = $parameterList = $open = $close = null;
        $static = null;

        if ($this->tokens->ifNextIs(Token::T_LABEL)) {
            if ($this->tokens->current->value === 'static') {
                $static = $this->tokens->chomp();
            }
        }

        if ($this->ifType() || $this->tokens->if(Token::T_VARIABLE)) {
            $type = $this->parseTypes();
        }

        if ($this->tokens->if(Token::T_LABEL)) {
            $name = $this->tokens->chomp();
        }

        if ($this->tokens->if(Token::T_PAREN_OPEN)) {
            $open = $this->tokens->chomp(Token::T_PAREN_OPEN);
            $parameterList = $this->parseParameterList();
            $close = $this->tokens->chompIf(Token::T_PAREN_CLOSE);
        }

        return new MethodTag($tag, $type, $name, $static, $open, $parameterList, $close, $this->parseText());
    }

    private function parseProperty(): PropertyTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $type = $name = null;
        if ($this->ifType()) {
            $type = $this->parseTypes();
        }
        if ($this->tokens->ifNextIs(Token::T_VARIABLE)) {
            $name = $this->tokens->chomp();
        }

        return new PropertyTag($tag, $type, $name);
    }

    private function parseTypes(): ?TypeNode
    {
        $type = $this->parseType();
        if (null === $type) {
            return $type;
        }
        $elements = [$type];
        $mode = null;

        while (true) {
            if (
                $this->tokens->if(Token::T_BAR) ||
                $this->tokens->if(Token::T_AMPERSAND)
            ) {
                $delimiter = $this->tokens->chomp();
                if (!$mode) {
                    $mode = $delimiter->type;
                }

                if ($mode !== $delimiter->type) {
                    continue;
                }

                $elements[] = $delimiter;
                $type = $this->parseType();
                if (null !== $type) {
                    $elements[] = $type;
                    continue;
                }
            }
            break;
        }

        $list = new TypeList($elements);

        if (count($list->list) === 1) {
            return $list->types()->first();
        }

        if ($mode && $mode === Token::T_AMPERSAND) {
            return new IntersectionNode($list);
        }
        return new UnionNode($list);
    }

    private function parseType(): ?TypeNode
    {
        if (null === $this->tokens->current) {
            return null;
        }

        if ($this->tokens->current->type === Token::T_VARIABLE) {
            if ($this->tokens->current->value === '$this') {
                $variable = $this->tokens->chomp(Token::T_VARIABLE);
                return new ThisNode($variable);
            }
            return $this->parseConditionalType();
        }

        if ($this->tokens->current->type === Token::T_NULLABLE) {
            $nullable = $this->tokens->chomp();
            return new NullableNode($nullable, $this->parseTypes());
        }

        if ($this->tokens->current->type === Token::T_PAREN_OPEN) {
            $open = $this->tokens->chomp();
            $this->tokens->chompWhitespace();
            $type = $this->parseTypes();
            $this->tokens->chompWhitespace();
            $close = $this->tokens->chompIf(Token::T_PAREN_CLOSE);

            return new ParenthesizedType($open, $type, $close);
        }

        $type = $this->tokens->chomp();

        if (null === $this->tokens->current) {
            return $this->createTypeFromToken($type);
        }

        $isList = false;

        if ($this->tokens->current->type === Token::T_PAREN_OPEN) {
            $open = $this->tokens->chomp();

            $typeList = null;
            if ($this->tokens->if(Token::T_LABEL)) {
                $typeList = $this->parseTypeList();
            }

            $close = $this->tokens->chomp();
            $returnType = null;
            $colon = null;

            if ($this->tokens->if(Token::T_COLON)) {
                $colon = $this->tokens->chomp();
                if ($this->tokens->if(Token::T_LABEL)) {
                    $returnType = $this->parseTypes();
                }
            }

            return new CallableNode(
                $type,
                $open,
                $typeList,
                $close,
                $colon,
                $returnType,
            );
        }

        if ($this->tokens->current->type === Token::T_BRACKET_ANGLE_OPEN) {
            $open = $this->tokens->chomp();
            $typeList = null;
            if ($this->tokens->if(Token::T_VARIABLE)) {
                $typeList = $this->parseTypeList();
            }
            if ($this->tokens->if(Token::T_QUOTED_STRING)) {
                $typeList = $this->parseTypeList();
            }
            if ($this->tokens->if(Token::T_LABEL)) {
                $typeList = $this->parseTypeList();
            }
            if ($this->tokens->if(Token::T_INTEGER)) {
                $typeList = $this->parseTypeList();
            }

            if (!$this->tokens->if(Token::T_BRACKET_ANGLE_CLOSE)) {
                return null;
            }

            if (!$typeList) {
                return null;
            }

            $type = new GenericNode(
                $open,
                $this->createTypeFromToken($type),
                $typeList,
                $this->tokens->chomp()
            );
            return $this->parseDimensions($type);
        }

        if ($this->tokens->current->type === Token::T_BRACKET_CURLY_OPEN) {
            $open = $this->tokens->chomp();
            assert(!is_null($open));
            $keyValues = [];
            $close = null;
            if ($this->tokens->if(Token::T_LABEL)) {
                $keyValues = $this->parseArrayKeyValues();
            }
            if ($this->tokens->if(Token::T_BRACKET_CURLY_CLOSE)) {
                $close = $this->tokens->chomp();
            }

            $type = new ArrayShapeNode($open, new ArrayKeyValueList(
                $keyValues,
            ), $close);

            return $this->parseDimensions($type);
        }

        return $this->parseDimensions($this->createTypeFromToken($type));
    }

    private function parseDimensions(TypeNode $type): TypeNode
    {
        while ($this->tokens->if(Token::T_LIST)) {
            $list = $this->tokens->chomp();
            $type = new ListBracketsNode($type, $list);
        }

        return $type;
    }

    private function createTypeFromToken(Token $type): TypeNode
    {
        if (strtolower($type->value) === 'null') {
            return new NullNode($type);
        }
        if (strtolower($type->value) === 'array') {
            return new ArrayNode($type);
        }
        if (strtolower($type->value) === 'list') {
            return new ListNode($type);
        }
        if (in_array($type->value, self::SCALAR_TYPES)) {
            return new ScalarNode($type);
        }
        if ($type->type === Token::T_QUOTED_STRING) {
            return new LiteralStringNode($type);
        }
        if ($type->type === Token::T_FLOAT) {
            return new LiteralFloatNode($type);
        }
        if ($type->type === Token::T_INTEGER) {
            return new LiteralIntegerNode($type);
        }
        if ($type->type !== Token::T_LABEL) {
            return new UnsupportedNode($type);
        }

        $classNode = new ClassNode($type);

        if (
            $this->tokens->peekIs(0, Token::T_DOUBLE_COLON) &&
            ($this->tokens->peekIs(1, Token::T_LABEL) || $this->tokens->peekIs(1, Token::T_ASTERISK))
        ) {
            return new ConstantNode(
                $classNode,
                $this->tokens->chomp(),
                $this->tokens->chomp(),
            );
        }

        return $classNode;
    }

    private function parseVariable(): ?VariableNode
    {
        if ($this->tokens->current->type !== Token::T_VARIABLE) {
            return null;
        }

        $name = $this->tokens->chomp(Token::T_VARIABLE);

        return new VariableNode($name);
    }

    private function parseTypeList(string $delimiter = ','): TypeList
    {
        $types = [];
        while (true) {
            if ($this->tokens->if(Token::T_LABEL)) {
                $types[] = $this->parseTypes();
            } elseif ($this->tokens->if(Token::T_QUOTED_STRING)) {
                $types[] = $this->parseTypes();
            } elseif ($this->tokens->if(Token::T_INTEGER)) {
                $types[] = $this->parseTypes();
            } elseif ($this->tokens->if(Token::T_VARIABLE)) {
                $types[] = $this->parseTypes();
            }
            if ($this->tokens->if(Token::T_COMMA)) {
                $types[] = $this->tokens->chomp();
                continue;
            }
            break;
        }

        return new TypeList($types);
    }

    private function parseParameterList(): ?ParameterList
    {
        if ($this->tokens->if(Token::T_PAREN_CLOSE)) {
            return null;
        }

        $parameters = [];
        while (true) {
            $parameters[] = $this->parseParameter();
            if ($this->tokens->if(Token::T_COMMA)) {
                $parameters[] = $this->tokens->chomp();
                continue;
            }
            break;
        }

        return new ParameterList($parameters);
    }

    private function parseParameter(): ParameterTag
    {
        $type = $name = $default = null;
        if ($this->tokens->if(Token::T_LABEL)) {
            $type = $this->parseTypes();
        }
        if ($this->tokens->if(Token::T_VARIABLE)) {
            $name = $this->parseVariable();
        }
        if ($this->tokens->if(Token::T_EQUALS)) {
            $equals = $this->tokens->chomp();
            $default = $this->parseValue();
        }
        return new ParameterTag($type, $name, $default);
    }

    private function parseDeprecated(): DeprecatedTag
    {
        return new DeprecatedTag(
            $this->tokens->chomp(Token::T_TAG),
            $this->parseText()
        );
    }

    private function parseMixin(): MixinTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $type = null;

        if ($this->tokens->if(Token::T_LABEL)) {
            $type = $this->parseTypes();
            if (!$type instanceof ClassNode) {
                $type = null;
            }
        }

        return new MixinTag($tag, $type);
    }

    private function parseReturn(): ReturnTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $type = null;

        if ($this->ifType()) {
            $type = $this->parseTypes();
        }

        if ($this->tokens->if(Token::T_VARIABLE)) {
            $variable = $this->tokens->chomp(Token::T_VARIABLE);
            if ($variable->value === '$this') {
                $type = new ThisNode($variable);
            }
        }

        return new ReturnTag($tag, $type, $this->parseText());
    }

    /**
     * Parse text until the next tag
     *
     * This method assumes that any prose after a tag belongs to the tag.
     */
    private function parseText(): ?TextNode
    {
        if (null === $this->tokens->current) {
            return null;
        }

        $text = [];

        while ($this->tokens->current) {
            if ($this->tokens->current->type === Token::T_PHPDOC_CLOSE) {
                break;
            }
            if ($this->tokens->current->type === Token::T_TAG) {
                break;
            }
            $text[] = $this->tokens->chomp();
        }

        if ($text) {
            return new TextNode($text);
        }

        return null;
    }

    private function ifType(): bool
    {
        return $this->tokens->if(Token::T_LABEL) ||
            $this->tokens->if(Token::T_NULLABLE) ||
            $this->tokens->if(Token::T_QUOTED_STRING) ||
            $this->tokens->if(Token::T_INTEGER) ||
            $this->tokens->if(Token::T_FLOAT) ||
            $this->tokens->if(Token::T_PAREN_OPEN);
    }

    private function parseValue(): ?ValueNode
    {
        if ($this->tokens->if(Token::T_LABEL)) {
            if (strtolower($this->tokens->current->value) === 'null') {
                return new NullValue($this->tokens->chomp());
            }
        }

        return null;
    }

    private function parseTemplate(): TemplateTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $placeholder = null;
        $of = null;
        $type = null;

        if ($this->tokens->if(Token::T_LABEL)) {
            $placeholder = $this->tokens->chomp();
        }

        if ($this->tokens->if(Token::T_LABEL)) {
            $of = $this->tokens->chomp();
            if ($of->value === 'of') {
                /** @phpstan-ignore-next-line */
                if ($this->tokens->if(Token::T_LABEL)) {
                    $type = $this->parseTypes();
                }
            } else {
                $of = null;
            }
        }

        return new TemplateTag($tag, $placeholder, $of, $type);
    }

    private function parseExtends(): ExtendsTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $type = null;

        if ($this->tokens->if(Token::T_LABEL)) {
            $type = $this->parseTypes();
        }

        return new ExtendsTag($tag, $type);
    }

    private function parseImplements(): ImplementsTag
    {
        $tag = $this->tokens->chomp(Token::T_TAG);
        $types = [];

        if ($this->tokens->if(Token::T_LABEL)) {
            $types = $this->parseTypeList()->list;
        }

        return new ImplementsTag($tag, $types);
    }

    /**
     * @return array<int,ArrayKeyValueNode|Token>
     */
    private function parseArrayKeyValues(): array
    {
        if ($this->tokens->if(Token::T_BRACKET_CURLY_CLOSE)) {
            return [];
        }

        $list = [];
        while (true) {
            /** @phpstan-ignore-next-line Condition is not always false */
            if ($this->tokens->if(Token::T_BRACKET_CURLY_CLOSE)) {
                break;
            }
            $list[] = $this->parseArrayKeyValue();
            if ($this->tokens->if(Token::T_COMMA)) {
                $token = $this->tokens->chomp();
                if ($token) {
                    $list[] = $token;
                }
                continue;
            }
            break;
        }

        return $list;
    }

    private function parseArrayKeyValue(): ArrayKeyValueNode
    {
        $key = $colon = $type = null;

        if (
            $this->tokens->if(Token::T_LABEL) &&
            $this->tokens->peekIs(1, Token::T_COLON)
        ) {
            $key = $this->tokens->chomp();
            $colon = $this->tokens->chomp();
        }

        if (
            $this->tokens->if(Token::T_LABEL) &&
            $this->tokens->peekIs(1, Token::T_NULLABLE) &&
            $this->tokens->peekIs(2, Token::T_COLON)
        ) {
            $key = $this->tokens->chomp();
            $_ = $this->tokens->chomp();
            $colon = $this->tokens->chomp();
        }

        $type = null;
        if ($this->tokens->if(Token::T_LABEL)) {
            $type = $this->parseTypes();
        }

        return new ArrayKeyValueNode($key, $colon, $type);
    }

    private function parseConditionalType(): TypeNode
    {
        $variable = $this->parseVariable();
        if (!$this->tokens->if(Token::T_LABEL)) {
            return new ConditionalNode($variable);
        }
        $is = $this->tokens->chomp();
        if ($is->toString() !== 'is') {
            return new ConditionalNode($variable, $is);
        }
        $this->tokens->chompWhitespace();
        $isType = $this->parseType();
        $this->tokens->chompWhitespace();
        if (!$question = $this->tokens->chompIf(Token::T_NULLABLE)) {
            return new ConditionalNode($variable, $is, $isType);
        }
        $this->tokens->chompWhitespace();
        $left = $this->parseTypes();
        $this->tokens->chompWhitespace();
        if (!$colon = $this->tokens->chompIf(Token::T_COLON)) {
            return new ConditionalNode($variable, $is, $isType);
        }
        $this->tokens->chompWhitespace();
        $right = $this->parseTypes();

        return new ConditionalNode($variable, $is, $isType, $question, $left, $colon, $right);
    }
}
