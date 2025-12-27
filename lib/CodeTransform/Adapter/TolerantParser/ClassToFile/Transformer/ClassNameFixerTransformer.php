<?php

namespace Phpactor\CodeTransform\Adapter\TolerantParser\ClassToFile\Transformer;

use Phpactor\WorseReflection\Bridge\TolerantParser\AstProvider\TolerantAstProvider;
use Amp\Promise;
use Amp\Success;
use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\AstProvider;
use Microsoft\PhpParser\Token;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use RuntimeException;

class ClassNameFixerTransformer implements Transformer
{
    public function __construct(
        private readonly FileToClass $fileToClass,
        private readonly AstProvider $parser = new TolerantAstProvider(),
    ) {
    }

    /**
     * @return Promise<TextEdits>
     */
    public function transform(SourceCode $code): Promise
    {
        if ($code->uri()->scheme() !== 'file') {
            throw new TransformException(sprintf('Source is not a file:// it is "%s"', $code->uri()->scheme()));
        }
        $classFqn = $this->determineClassFqn($code);
        $correctClassName = $classFqn->name();
        $correctNamespace = $classFqn->namespace();

        $rootNode = $this->parser->get($code);
        $edits = [];

        if ($textEdit = $this->fixNamespace($rootNode, $correctNamespace)) {
            $edits[] = $textEdit;
        }

        if ($textEdit = $this->fixClassName($rootNode, $correctClassName)) {
            $edits[] = $textEdit;
        }

        return new Success(TextEdits::fromTextEdits($edits));
    }


    /**
     * @return Promise<Diagnostics>
     */
    public function diagnostics(SourceCode $code): Promise
    {
        if ($code->uri()->scheme() !== 'file') {
            return new Success(Diagnostics::none());
        }
        $rootNode = $this->parser->get($code);
        try {
            $classFqn = $this->determineClassFqn($code);
        } catch (RuntimeException) {
            return new Success(Diagnostics::none());
        }
        $correctClassName = $classFqn->name();
        $correctNamespace = $classFqn->namespace();

        $diagnostics = [];

        if (null !== $this->fixNamespace($rootNode, $correctNamespace)) {
            $namespaceDefinition = $rootNode->getFirstDescendantNode(NamespaceDefinition::class);
            $diagnostics[] = new Diagnostic(
                ByteOffsetRange::fromInts(
                    $namespaceDefinition ? $namespaceDefinition->getStartPosition() : 0,
                    $namespaceDefinition ? $namespaceDefinition->getEndPosition() : 0,
                ),
                sprintf('Namespace should probably be "%s"', $correctNamespace),
                Diagnostic::WARNING
            );
        }
        if (null !== $edits = $this->fixClassName($rootNode, $correctClassName)) {
            $classLike = $rootNode->getFirstDescendantNode(ClassLike::class);
            $nameToken = $this->nameToken($classLike);

            if ($nameToken) {
                $diagnostics[] = new Diagnostic(
                    ByteOffsetRange::fromInts(
                        $nameToken->getStartPosition(),
                        $nameToken->getEndPosition(),
                    ),
                    sprintf('Class name should probably be "%s"', $correctClassName),
                    Diagnostic::WARNING
                );
            }
        }

        return new Success(new Diagnostics($diagnostics));
    }


    private function fixClassName(SourceFileNode $rootNode, string $correctClassName): ?TextEdit
    {
        $classLike = $rootNode->getFirstDescendantNode(ClassLike::class);

        if (null === $classLike) {
            return null;
        }

        assert($classLike instanceof EnumDeclaration || $classLike instanceof ClassDeclaration || $classLike instanceof InterfaceDeclaration || $classLike instanceof TraitDeclaration);

        $name = $classLike->name->getText($rootNode->getFileContents());

        if (!is_string($name) || $name === $correctClassName) {
            return null;
        }

        return TextEdit::create($classLike->name->start, strlen($name), $correctClassName);
    }

    private function fixNamespace(SourceFileNode $rootNode, string $correctNamespace): ?TextEdit
    {
        $namespaceDefinition = $rootNode->getFirstDescendantNode(NamespaceDefinition::class);
        assert($namespaceDefinition instanceof NamespaceDefinition || is_null($namespaceDefinition));
        $statement = sprintf('namespace %s;', $correctNamespace);

        if ($correctNamespace && null === $namespaceDefinition) {
            $scriptStart = $rootNode->getFirstDescendantNode(InlineHtml::class);
            $scriptStart = $scriptStart ? $scriptStart->getEndPosition() : 0;

            $statement = "\n" . $statement . "\n";

            if (0 === $scriptStart) {
                $statement = '<?php' . "\n" . $statement;
            }


            return TextEdit::create($scriptStart, 0, $statement);
        }

        if (null === $namespaceDefinition) {
            return null;
        }

        if ($namespaceDefinition->name instanceof QualifiedName) {
            if ($namespaceDefinition->name->__toString() === $correctNamespace) {
                return null;
            }
        }

        return TextEdit::create(
            $namespaceDefinition->getStartPosition(),
            $namespaceDefinition->getEndPosition() - $namespaceDefinition->getStartPosition(),
            $statement
        );
    }

    private function determineClassFqn(SourceCode $code): ClassName
    {
        if (!$code->uri()->path()) {
            throw new RuntimeException('Source code has no path associated with it');
        }

        $candidates = $this->fileToClass->fileToClassCandidates(
            FilePath::fromString((string) $code->uri()->path())
        );

        $classFqn = $candidates->best();

        return $classFqn;
    }

    private function nameToken(?Node $classLike): ?Token
    {
        if (null === $classLike) {
            return null;
        }

        if (!property_exists($classLike, 'name')) {
            return null;
        }

        $name = $classLike->name;

        if (!$name instanceof Token) {
            return null;
        }

        return $name;
    }
}
