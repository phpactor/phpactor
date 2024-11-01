<?php

namespace Phpactor\WorseReflection\Bridge\PHPStan\DocblockParser;

use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MethodTagValueParameterNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MixinTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\WorseReflection\Core\Deprecation;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockParam;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockParams;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeAlias;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeAliases;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockTypeAssertion;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVar;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockVars;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection as CoreReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as CoreReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\TemplateMap;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionMethod;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionParameter;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionProperty;
use Phpactor\WorseReflection\Core\Visibility;
use function array_map;

class PHPStanParsedDocblock implements DocBlock
{
    public function __construct(private PhpDocNode $node, private PHPStanTypeConverter $typeConverter, private string $raw)
    {
    }

    public function rawNode(): PhpDocNode
    {
        return $this->node;
    }

    /**
     * @return Types<Type>
     */
    public function types(): Types
    {
        $types = [];
        foreach ($this->node->getTags() as $tag) {
            $type = match (true) {
                $tag->value instanceof MethodTagValueNode => $tag->value->returnType,
                property_exists($tag->value, 'type') => $tag->value->type,
            };
            $types[] = $this->typeConverter->convert($type);
        }

        return new Types($types);
    }

    public function typeAliases(): DocBlockTypeAliases
    {
        $types = [];
        foreach (['@phan-type', '@psalm-type', '@phpstan-type'] as $tagName) {
            foreach ($this->node->getTypeAliasTagValues($tagName) as $tag) {
                $types[] = new DocBlockTypeAlias(
                    $tag->alias,
                    $this->typeConverter->convert($tag->type),
                );
            }
        }

        return new DocBlockTypeAliases($types);
    }

    public function methodType(string $methodName): Type
    {
        foreach (['@method', '@phan-method', '@psalm-method', '@phpstan-method'] as $tagName) {
            foreach ($this->node->getMethodTagValues($tagName) as $methodTag) {
                if ($methodTag->methodName !== $methodName) {
                    continue;
                }
                return $this->convertType($methodTag->returnType);
            }
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
        foreach (['@var', '@phan-var', '@psalm-var', '@phpstan-var'] as $tagName) {
            foreach ($this->node->getVarTagValues($tagName) as $varTag) {
                $vars[] = new DocBlockVar(
                    ltrim($varTag->variableName, '$'),
                    $this->convertType($varTag->type),
                );
            }
        }

        return new DocBlockVars($vars);
    }

    public function params(): DocBlockParams
    {
        $params = [];
        foreach (['@param', '@phan-param', '@psalm-param', '@phpstan-param'] as $tagName) {
            foreach ($this->node->getParamTagValues($tagName) as $paramTag) {
                $params[] = new DocBlockParam(
                    ltrim($paramTag->parameterName, '$'),
                    $this->convertType($paramTag->type),
                );
            }
        }

        return new DocBlockParams($params);
    }

    public function parameterType(string $paramName): Type
    {
        $types = [];
        foreach (['@param', '@phan-param', '@psalm-param', '@phpstan-param'] as $tagName) {
            foreach ($this->node->getParamTagValues($tagName) as $paramTag) {
                if (ltrim($paramTag->parameterName, '$') !== $paramName) {
                    continue;
                }
                return $this->convertType($paramTag->type);
            }
        }

        return TypeFactory::undefined();
    }

    public function propertyType(string $propertyName): Type
    {
        $types = [];
        foreach (['@property', '@phpstan-property'] as $tagName) {
            foreach ($this->node->getPropertyTagValues($tagName) as $propertyTag) {
                if (ltrim($propertyTag->propertyName, '$') !== $propertyName) {
                    continue;
                }
                return $this->convertType($propertyTag->type);
            }
        }

        return TypeFactory::undefined();
    }

    public function formatted(): string
    {
        return implode(
            "\n",
            array_map(
                static fn (string $line) => preg_replace('{^\s+}', '', $line),
                explode(
                    "\n",
                    $this->node->__toString()
                )
            )
        );
    }

    public function returnType(): Type
    {
        foreach (['@return', '@phan-return', '@phan-real-return', '@psalm-return', '@phpstan-return'] as $tagName) {
            foreach ($this->node->getReturnTagValues($tagName) as $tag) {
                return $this->convertType($tag->type);
            }
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
        //todo handle @property-read/@property-write
        foreach (['@property', '@phpstan-property'] as $tagName) {
            foreach ($this->node->getPropertyTagValues($tagName) as $propertyTag) {
                $type = $this->convertType($propertyTag->type);
                $property = new VirtualReflectionProperty(
                    $declaringClass->position(),
                    $declaringClass,
                    $declaringClass,
                    ltrim($propertyTag->propertyName, '$'),
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
        }

        return ReflectionPropertyCollection::fromReflectionProperties($properties);
    }

    public function methods(ReflectionClassLike $declaringClass): CoreReflectionMethodCollection
    {
        $methods = [];
        foreach (['@method', '@phan-method', '@psalm-method', '@phpstan-method'] as $tagName) {
            foreach ($this->node->getMethodTagValues($tagName) as $methodTag) {
                $params = ReflectionParameterCollection::empty();
                $method = new VirtualReflectionMethod(
                    $declaringClass->position(),
                    $declaringClass,
                    $declaringClass,
                    $methodTag->methodName,
                    new Frame(),
                    $this,
                    $declaringClass->scope(),
                    Visibility::public(),
                    $this->convertType($methodTag->returnType),
                    $this->convertType($methodTag->returnType),
                    $params,
                    NodeText::fromString(''),
                    false,
                    $methodTag->isStatic,
                    new Deprecation(false),
                );
                $this->addParameters($method, $params, $methodTag->parameters);
                $methods[] = $method;
            }
        }

        return ReflectionMethodCollection::fromReflectionMethods($methods);
    }

    public function deprecation(): Deprecation
    {
        foreach ($this->node->getDeprecatedTagValues() as $deprecatedTag) {
            return new Deprecation(true, $deprecatedTag->description);
        }

        return new Deprecation(false);
    }

    public function templateMap(): TemplateMap
    {
        $map = [];
        foreach (['@template',  '@phpstan-template', '@psalm-template'] as $tagName) {
            foreach ($this->node->getTemplateTagValues($tagName) as $templateTag) {
                $map[$templateTag->name] = $this->convertType($templateTag->bound);
            }
        }

        return new TemplateMap($map);
    }

    public function extends(): array
    {
        $extends = [];
        foreach (['@extends', '@phan-extends', '@phan-inherits', '@template-extends', '@phpstan-extends'] as $tagName) {
            foreach ($this->node->getExtendsTagValues($tagName) as $extendsTag) {
                $extends[] = $this->convertType($extendsTag->type);
            }
        }

        return $extends;
    }

    public function implements(): array
    {
        $implements = [];
        foreach (['@implements', '@template-implements', '@phpstan-implements'] as $tagName) {
            foreach ($this->node->getImplementsTagValues($tagName) as $implementsTag) {
                $implements[] = $this->convertType($implementsTag->type);
            }
        }

        return $implements;
    }

    public function mixins(): array
    {
        return array_map(
            fn (MixinTagValueNode $mixinTag): Type => $this->convertType($mixinTag->type),
            $this->node->getMixinTagValues()
        );
    }

    public function assertions(): array
    {
        $assertions = [];
        foreach (['@phpstan-assert', '@psalm-assert'] as $tagName) {
            foreach ($this->node->getAssertTagValues($tagName) as $assert) {
                if (!$assert->parameter) {
                    continue;
                }
                $assertions[] = new DocBlockTypeAssertion(
                    ltrim($assert->parameter, '$'),
                    $this->convertType($assert->type),
                    $assert->isNegated === true
                );
            }
        }
        return $assertions;
    }

    /**
     * @param MethodTagValueParameterNode[] $parameterList
     */
    private function addParameters(VirtualReflectionMethod $method, ReflectionParameterCollection $collection, array $parameterList): void
    {
        foreach ($parameterList as $index => $parameterTag) {
            $type = $this->convertType($parameterTag->type);
            $collection->add(new VirtualReflectionParameter(
                ltrim($parameterTag->parameterName, '$'),
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
