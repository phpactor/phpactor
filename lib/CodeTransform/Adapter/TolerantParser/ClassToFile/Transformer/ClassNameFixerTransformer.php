<?php

namespace Phpactor\CodeTransform\Adapter\TolerantParser\ClassToFile\Transformer;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Parser;
use Phpactor\ClassFileConverter\Domain\ClassName;
use Phpactor\ClassFileConverter\Domain\FilePath;
use Phpactor\ClassFileConverter\Domain\FileToClass;
use Phpactor\CodeTransform\Domain\Diagnostic;
use Phpactor\CodeTransform\Domain\Diagnostics;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformer;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;
use RuntimeException;

class ClassNameFixerTransformer implements Transformer
{
    /**
     * @var FileToClass
     */
    private $fileToClass;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(FileToClass $fileToClass, Parser $parser = null)
    {
        $this->fileToClass = $fileToClass;
        $this->parser = $parser ?: new Parser();
    }

    public function transform(SourceCode $code): TextEdits
    {
        $classFqn = $this->determineClassFqn($code);
        $correctClassName = $classFqn->name();
        $correctNamespace = $classFqn->namespace();

        $rootNode = $this->parser->parseSourceFile((string) $code);
        $edits = [];

        if ($textEdit = $this->fixNamespace($rootNode, $correctNamespace)) {
            $edits[] = $textEdit;
        }

        if ($textEdit = $this->fixClassName($rootNode, $correctClassName)) {
            $edits[] = $textEdit;
        }

        return TextEdits::fromTextEdits($edits);
    }

    /**
     * {@inheritDoc}
     */
    public function diagnostics(SourceCode $code): Diagnostics
    {
        $rootNode = $this->parser->parseSourceFile((string) $code);
        try {
            $classFqn = $this->determineClassFqn($code);
        } catch (RuntimeException $couldNotFindCandidate) {
            return Diagnostics::none();
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

            $diagnostics[] = new Diagnostic(
                ByteOffsetRange::fromInts(
                    $classLike ? $classLike->getStartPosition() : 0,
                    $classLike ? $classLike->getEndPosition() : 0
                ),
                sprintf('Class name should probably be "%s"', $correctClassName),
                Diagnostic::WARNING
            );
        }

        return new Diagnostics($diagnostics);
    }

    /**
     * @return TextEdit|null
     */
    private function fixClassName(SourceFileNode $rootNode, string $correctClassName): ?TextEdit
    {
        $classLike = $rootNode->getFirstDescendantNode(ClassLike::class);
        
        if (null === $classLike) {
            return null;
        }
        
        assert($classLike instanceof ClassDeclaration || $classLike instanceof InterfaceDeclaration || $classLike instanceof TraitDeclaration);
        
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

            $statement = PHP_EOL . $statement . PHP_EOL;

            if (0 === $scriptStart) {
                $statement = '<?php' . PHP_EOL . $statement;
            }


            return TextEdit::create($scriptStart, 0, $statement);
        }

        if (null === $namespaceDefinition) {
            return null;
        }

        if ($namespaceDefinition->name) {
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
        if (!$code->path()) {
            throw new RuntimeException('Source code has no path associated with it');
        }
        
        $candidates = $this->fileToClass->fileToClassCandidates(
            FilePath::fromString((string) $code->path())
        );
        
        $classFqn = $candidates->best();

        return $classFqn;
    }
}
