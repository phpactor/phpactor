<?php

namespace Phpactor\CodeTransform\Adapter\WorseReflection\Transformer;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable as MicrosoftVariable;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Parser;
use Microsoft\PhpParser\Token;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextEdits;
use Phpactor\WorseReflection\Core\Reflection\ReflectionTrait;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode as WorseSourceCode;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\ClassLikeBuilder;
use Phpactor\CodeBuilder\Domain\Builder\ClassBuilder;
use Phpactor\CodeBuilder\Domain\Builder\TraitBuilder;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;

class AddMissingProperties implements Transformer
{
    private const LENGTH_OF_THIS_PREFIX = 7;

    private Reflector $reflector;

    private Updater $updater;

    private Parser $parser;

    public function __construct(Reflector $reflector, Updater $updater, ?Parser $parser = null)
    {
        $this->updater = $updater;
        $this->reflector = $reflector;
        $this->parser = $parser ?: new Parser();
    }

    public function transform(SourceCode $code): TextEdits
    {
        $rootNode = $this->parser->parseSourceFile($code->__toString());

        $classes = $this->reflector->reflectClassesIn(
            WorseSourceCode::fromString((string) $code)
        );

        if ($classes->count() === 0) {
            return TextEdits::none();
        }

        $sourceBuilder = SourceCodeBuilder::create();

        /** @var ReflectionClassLike $class */
        foreach ($classes as $class) {
            $classBuilder = $this->resolveClassBuilder($sourceBuilder, $class);

            foreach ($this->missingPropertyNames($rootNode, $class) as [$memberName, $token, $expression]) {
                assert($expression instanceof Node);
                $offset = $this->reflector->reflectOffset($code->__toString(), $expression->getEndPosition());
                $propertyBuilder = $classBuilder
                    ->property($memberName)
                    ->visibility('private');

                $type = $offset->symbolContext()->type();
                if ($type->isDefined()) {
                    foreach ($type->classNamedTypes() as $importClass) {
                        $sourceBuilder->use($importClass->name()->__toString());
                    }
                    $type = $type->toLocalType($class->scope());
                    $propertyBuilder->type($type->toPhpString());
                    $propertyBuilder->docType((string)$type->generalize());
                }
            }
        }

        if (isset($class)) {
            $sourceBuilder->namespace($class->name()->namespace());
        }

        return $this->updater->textEditsFor(
            $sourceBuilder->build(),
            Code::fromString((string) $code)
        );
    }

    public function diagnostics(SourceCode $code): Diagnostics
    {
        $diagnostics = [];
        $classes = $this->reflector->reflectClassesIn($code->__toString());

        if ($classes->count() === 0) {
            return new Diagnostics([]);
        }

        $rootNode = $this->parser->parseSourceFile($code->__toString());

        /** @var ReflectionClassLike $class */
        foreach ($classes as $class) {
            foreach ($this->missingPropertyNames($rootNode, $class) as [$memberName, $token, $expression]) {
                assert($token instanceof Token);
                assert($expression instanceof Expression);
                $diagnostics[] = new Diagnostic(
                    ByteOffsetRange::fromInts(
                        $token->getStartPosition(),
                        $token->getEndPosition()
                    ),
                    sprintf('Assigning to non existant property "%s"', $memberName),
                    Diagnostic::WARNING
                );
            }
        }

        return new Diagnostics($diagnostics);
    }

    /**
     * @return TraitBuilder|ClassBuilder
     */
    private function resolveClassBuilder(SourceCodeBuilder $sourceBuilder, ReflectionClassLike $class): ClassLikeBuilder
    {
        $name = $class->name()->short();

        if ($class->isTrait()) {
            return $sourceBuilder->trait($name);
        }

        return $sourceBuilder->class($name);
    }

    /**
     * @return Generator<array{string, Token, Expression}>
     */
    private function missingPropertyNames(SourceFileNode $rootNode, ReflectionClassLike $class): Generator
    {
        $classNode = $rootNode->getDescendantNodeAtPosition($class->position()->start() + 1);

        if (!$classNode instanceof ClassDeclaration && !$classNode instanceof TraitDeclaration) {
            return;
        }

        if (!$class instanceof ReflectionClass && !$class instanceof ReflectionTrait) {
            return;
        }

        foreach ($classNode->getDescendantNodes() as $assignmentExpression) {
            if (!$assignmentExpression instanceof AssignmentExpression) {
                continue;
            }

            $memberAccess = $assignmentExpression->leftOperand;
            if (!$memberAccess instanceof MemberAccessExpression) {
                continue;
            }


            $deref = $memberAccess->dereferencableExpression;

            if (!$deref instanceof MicrosoftVariable) {
                continue;
            }

            if ($deref->getText() !== '$this') {
                continue;
            }

            $memberNameToken = $memberAccess->memberName;

            if (!$memberNameToken instanceof Token) {
                continue;
            }

            $memberName = $memberNameToken->getText($rootNode->getFileContents());

            if (!is_string($memberName)) {
                continue;
            }

            $rightOperand = $assignmentExpression->rightOperand;

            if (!$rightOperand instanceof Expression) {
                continue;
            }

            if ($class->properties()->has($memberName)) {
                continue;
            }

            yield [$memberName, $memberNameToken, $rightOperand];
        }
    }
}
