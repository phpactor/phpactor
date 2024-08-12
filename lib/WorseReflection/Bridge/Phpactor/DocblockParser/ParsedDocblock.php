<?php

namespace Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser;

use Phpactor\DocblockParser\Ast\Docblock as ParserDocblock;
use Phpactor\DocblockParser\Ast\ParameterList;
use Phpactor\DocblockParser\Ast\Tag\AssertTag;
use Phpactor\DocblockParser\Ast\Tag\DeprecatedTag;
use Phpactor\DocblockParser\Ast\Tag\ExtendsTag;
use Phpactor\DocblockParser\Ast\Tag\ImplementsTag;
use Phpactor\DocblockParser\Ast\Tag\MethodTag;
use Phpactor\DocblockParser\Ast\Tag\MixinTag;
use Phpactor\DocblockParser\Ast\Tag\ParamTag;
use Phpactor\DocblockParser\Ast\Tag\ParameterTag;
use Phpactor\DocblockParser\Ast\Tag\PropertyTag;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\DocblockParser\Ast\Tag\TemplateTag;
use Phpactor\DocblockParser\Ast\Tag\TypeAliasTag;
use Phpactor\DocblockParser\Ast\Tag\VarTag;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockParam;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockParams;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeAlias;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeAliases;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeAssertion;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;
use function array_map;

class ParsedDocblock implements DocBlock
{
    public function __construct(private ParserDocblock $node, private TypeConverter $typeConverter, private string $raw)
    {
    }

    public function rawNode(): ParserDocblock
    {
        return $this->node;
    }

    /**
     * @return Types<Type>
     */
    public function types(): Types
    {
        $types = [];
        foreach ($this->node->descendantElements(TypeNode::class) as $type) {
            if (!$type instanceof TypeNode) {
                continue;
            }
            $types[] = $this->typeConverter->convert($type);
        }

        return new Types($types);
    }

    public function typeAliases(): DocBlockTypeAliases
    {
        $types = [];
        foreach ($this->node->descendantElements(TypeAliasTag::class) as $tag) {
            $types[] = new DocBlockTypeAlias(
                $this->typeConverter->convert($tag->alias)->toPhpString(),
                $this->typeConverter->convert($tag->type),
            );
        }

        return new DocBlockTypeAliases($types);
    }

    public function methodType(string $methodName): Type
    {
        foreach ($this->node->tags(MethodTag::class) as $methodTag) {
            assert($methodTag instanceof MethodTag);
            if ($methodTag->methodName() !== $methodName) {
                continue;
            }
            $this->convertType($methodTag->type);
        }

        return TypeFactory::undefined();
    }

    public function inherits(): bool
    {
        return false;
    }

    public function vars(): DocBlockVars
    {
        $vars = [];
        foreach ($this->node->tags(VarTag::class) as $varTag) {
            assert($varTag instanceof VarTag);
            $vars[] = new DocBlockVar(
                $varTag->variable ? ltrim($varTag->name() ?? '', '$') : '',
                $this->convertType($varTag->type),
            );
        }

        return new DocBlockVars($vars);
    }

    public function params(): DocBlockParams
    {
        $params = [];
        foreach ($this->node->tags(ParamTag::class) as $paramTag) {
            $params[] = new DocBlockParam(
                $paramTag->paramName() ? ltrim(
                    /** @phpstan-ignore-next-line */
                    $paramTag->paramName() ?? '',
                    '$'
                ) : '',
                $this->convertType($paramTag->type),
            );
        }

        return new DocBlockParams($params);
    }

    public function parameterType(string $paramName): Type
    {
        $types = [];
        foreach ($this->node->tags(ParamTag::class) as $paramTag) {
            assert($paramTag instanceof ParamTag);
            if (ltrim($paramTag->paramName() ?? '', '$') !== $paramName) {
                continue;
            }
            return $this->convertType($paramTag->type);
        }

        return TypeFactory::undefined();
    }

    public function propertyType(string $propertyName): Type
    {
        $types = [];
        foreach ($this->node->tags(PropertyTag::class) as $propertyTag) {
            assert($propertyTag instanceof PropertyTag);
            if (ltrim($propertyTag->propertyName(), '$') !== $propertyName) {
                continue;
            }
            return $this->convertType($propertyTag->type);
        }

        return TypeFactory::undefined();
    }

    public function formatted(): string
    {
        return implode("\n", array_map(function (string $line) {
            return preg_replace('{^\s+}', '', $line);
        }, explode("\n", $this->node->prose())));
    }

    public function returnType(): Type
    {
        foreach ($this->node->tags(ReturnTag::class) as $tag) {
            assert($tag instanceof ReturnTag);
            return $this->convertType($tag->type());
        }

        return TypeFactory::undefined();
    }

    public function raw(): string
    {
        return $this->raw;
    }

    public function isDefined(): bool
    {
        return true;
    }

    public function properties(ReflectionClassLike $declaringClass): CoreReflectionPropertyCollection
    {
        $properties = [];
        foreach ($this->node->tags(PropertyTag::class) as $propertyTag) {
            assert($propertyTag instanceof PropertyTag);
            $type = $this->convertType($propertyTag->type);
            $property = new VirtualReflectionProperty(
                $declaringClass->position(),
                $declaringClass,
                $declaringClass,
                ltrim($propertyTag->propertyName() ?? '', '$'),
                new Frame(),
                $this,
                $declaringClass->scope(),
                Visibility::public(),
                $type,
                $type,
                new Deprecation(false),
            );
            $properties[] = $property;
        }

        return ReflectionPropertyCollection::fromReflectionProperties($properties);
    }

    public function methods(ReflectionClassLike $declaringClass): CoreReflectionMethodCollection
    {
        $methods = [];
        foreach ($this->node->tags(MethodTag::class) as $methodTag) {
            assert($methodTag instanceof MethodTag);
            $params = ReflectionParameterCollection::empty();
            $method = new VirtualReflectionMethod(
                $declaringClass->position(),
                $declaringClass,
                $declaringClass,
                $methodTag->methodName() ?? '',
                new Frame(),
                $this,
                $declaringClass->scope(),
                Visibility::public(),
                $this->convertType($methodTag->type),
                $this->convertType($methodTag->type),
                $params,
                NodeText::fromString(''),
                false,
                $methodTag->static ? true : false,
                new Deprecation(false),
            );
            $this->addParameters($method, $params, $methodTag->parameters);
            $methods[] = $method;
        }

        return ReflectionMethodCollection::fromReflectionMethods($methods);
    }

    public function deprecation(): Deprecation
    {
        foreach ($this->node->tags(DeprecatedTag::class) as $deprecatedTag) {
            assert($deprecatedTag instanceof DeprecatedTag);
            return new Deprecation(true, $deprecatedTag->text());
        }

        return new Deprecation(false);
    }

    public function templateMap(): TemplateMap
    {
        $map = [];
        foreach ($this->node->tags(TemplateTag::class) as $templateTag) {
            assert($templateTag instanceof TemplateTag);
            $map[$templateTag->placeholder()] = $this->convertType($templateTag->type);
        }
        return new TemplateMap($map);
    }

    public function extends(): array
    {
        $extends = [];
        foreach ($this->node->tags(ExtendsTag::class) as $extendsTag) {
            assert($extendsTag instanceof ExtendsTag);
            $extends[] = $this->convertType($extendsTag->type);
        }
        return $extends;
    }

    public function implements(): array
    {
        $implements = [];
        foreach ($this->node->tags(ImplementsTag::class) as $implementsTag) {
            assert($implementsTag instanceof ImplementsTag);
            $implements = array_merge($implements, array_map(function (TypeNode $type) {
                return $this->convertType($type);
            }, $implementsTag->types()));
        }
        return $implements;
    }

    public function mixins(): array
    {
        $mixins = [];
        foreach ($this->node->tags(MixinTag::class) as $mixinTag) {
            assert($mixinTag instanceof MixinTag);
            $mixins[] = $this->convertType($mixinTag->class);
        }
        return $mixins;
    }

    public function node(): ParserDocblock
    {
        return $this->node;
    }

    public function assertions(): array
    {
        $assertions = [];
        foreach ($this->node->tags(AssertTag::class) as $assert) {
            if (!$assert->paramName) {
                continue;
            }
            $assertions[] = new DocBlockTypeAssertion(
                ltrim($assert->paramName->toString(), '$'),
                $this->convertType($assert->type),
                $assert->negationOrEquality?->value === '!',
            );
        }
        return $assertions;
    }

    private function addParameters(VirtualReflectionMethod $method, ReflectionParameterCollection $collection, ?ParameterList $parameterList): void
    {
        if (null === $parameterList) {
            return;
        }
        foreach ($parameterList->parameters() as $index => $parameterTag) {
            assert($parameterTag instanceof ParameterTag);
            $type = $this->convertType($parameterTag->type);
            $collection->add(new VirtualReflectionParameter(
                ltrim($parameterTag->parameterName() ?? '', '$'),
                $method,
                $type,
                $type,
                DefaultValue::undefined(),
                false,
                $method->scope(),
                $method->position(),
                $index
            ));
        }
    }

    private function convertType(?TypeNode $type): Type
    {
        return $this->typeConverter->convert($type);
    }
}
