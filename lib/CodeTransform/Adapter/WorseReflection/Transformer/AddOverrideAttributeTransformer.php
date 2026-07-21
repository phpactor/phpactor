<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Amp\Promise;
use Amp\Success;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Phpactor\WorseReflection\Core\AstProvider;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Reflector;

class AddOverrideAttributeTransformer implements Transformer
{
    public function __construct(
        private Reflector $reflector,
        private string $phpVersion,
        private AstProvider $astProvider = new TolerantAstProvider(),
    ) {
    }

    /**
     * @return Promise<TextEdits>
     */
    public function transform(SourceCode $code): Promise
    {
        return new Success((function () use ($code) {
            $edits = [];
            foreach ($this->methodsNeedingAttribute($code) as [$method, $node]) {
                $edits[] = $this->createEdit($node, $code->__toString());
            }

            return TextEdits::fromTextEdits($edits);
        })());
    }

    /**
     * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $code): Promise
    {
        return new Success((function () use ($code) {
            $diagnostics = [];
            foreach ($this->methodsNeedingAttribute($code) as [$method, $node]) {
                $diagnostics[] = new Diagnostic(
                    $method->nameRange(),
                    sprintf(
                        'Method "%s" overrides a parent method but has no #[\Override] attribute',
                        $method->name(),
                    ),
                    Diagnostic::HINT
                );
            }

            /** @phpstan-ignore-next-line */
            return Diagnostics::fromArray($diagnostics);
        })());
    }

    /**
     * @return list<array{ReflectionMethod,MethodDeclaration}>
     */
    private function methodsNeedingAttribute(SourceCode $code): array
    {
        if (version_compare($this->phpVersion, '8.3', '<')) {
            return [];
        }

        $methodNodes = $this->methodNodesByNameOffset($code);
        $methods = [];

        foreach ($this->reflector->reflectClassesIn($code)->classes() as $class) {
            foreach ($class->methods()->belongingTo($class->name()) as $method) {
                $node = $methodNodes[$method->nameRange()->start()->toInt()] ?? null;

                if (null === $node) {
                    continue;
                }

                if ($this->hasOverrideAttribute($node)) {
                    continue;
                }

                if (!$this->overridesMethod($class, $method->name())) {
                    continue;
                }

                $methods[] = [$method, $node];
            }
        }

        return $methods;
    }

    private function overridesMethod(ReflectionClass $class, string $methodName): bool
    {
        // the engine does not consider a parent constructor or a private
        // parent method to be overridden, but an interface constructor is
        $parent = $class->parent();
        if (
            $parent && '__construct' !== $methodName
            && $parent->methods()->has($methodName)
            && !$parent->methods()->get($methodName)->visibility()->isPrivate()
        ) {
            return true;
        }

        foreach ($class->interfaces() as $interface) {
            if ($interface->methods()->has($methodName)) {
                return true;
            }
        }

        return false;
    }

    private function hasOverrideAttribute(MethodDeclaration $node): bool
    {
        foreach ($node->attributes ?? [] as $attributeGroup) {
            foreach ($attributeGroup->attributes->getElements() as $attribute) {
                if (!$attribute instanceof Attribute) {
                    continue;
                }
                $name = $attribute->name;
                $name = $name instanceof Node ? $name->getText() : $name->getText($node->getFileContents());

                if (strtolower(ltrim((string)$name, '\\')) === 'override') {
                    return true;
                }
            }
        }

        return false;
    }

    private function createEdit(MethodDeclaration $node, string $source): TextEdit
    {
        $start = $node->getStartPosition();
        $indent = '';
        $offset = $start;
        while ($offset > 0 && in_array($source[$offset - 1], [' ', "\t"], true)) {
            $indent = $source[$offset - 1] . $indent;
            $offset--;
        }

        if ($offset !== 0 && $source[$offset - 1] !== "\n") {
            return TextEdit::create($start, 0, '#[\Override] ');
        }

        return TextEdit::create($start, 0, sprintf("#[\Override]\n%s", $indent));
    }

    /**
     * @return array<int,MethodDeclaration>
     */
    private function methodNodesByNameOffset(SourceCode $code): array
    {
        $nodes = [];
        foreach ($this->astProvider->get($code)->getDescendantNodes() as $node) {
            if (!$node instanceof MethodDeclaration || null === $node->name) {
                continue;
            }
            $nodes[$node->name->getStartPosition()] = $node;
        }

        return $nodes;
    }
}
