<?php
namespace Phpactor\CodeBuilder\Adapter\TolerantParser;

use Microsoft\PhpParser\Node\SourceFileNode;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\InlineHtml;
use Microsoft\PhpParser\Node\Statement\NamespaceDefinition;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\EnumUpdater;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\UseStatementUpdater;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Prototype\NamespaceName;
use Phpactor\CodeBuilder\Domain\Prototype\Prototype;
use Phpactor\CodeBuilder\Domain\Prototype\SourceCode;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Util\TextFormat;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\ClassUpdater;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\InterfaceUpdater;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Updater\TraitUpdater;
use Phpactor\TextDocument\TextEdits;

class TolerantUpdater implements Updater
{
    private Parser $parser;

    private TextFormat $textFormat;

    private ClassUpdater $classUpdater;

    private InterfaceUpdater $interfaceUpdater;

    private TraitUpdater $traitUpdater;

    private EnumUpdater $enumUpdater;

    private UseStatementUpdater $useStatementUpdater;

    public function __construct(
        private Renderer $renderer,
        TextFormat $textFormat = null,
        Parser $parser = null
    ) {
        $this->parser = $parser ?: new Parser();
        $this->textFormat = $textFormat ?: new TextFormat();
        $this->classUpdater = new ClassUpdater($renderer);
        $this->interfaceUpdater = new InterfaceUpdater($renderer);
        $this->traitUpdater = new TraitUpdater($renderer);
        $this->enumUpdater = new EnumUpdater($renderer);
        $this->useStatementUpdater = new UseStatementUpdater();
    }

    public function textEditsFor(Prototype $prototype, Code $code): TextEdits
    {
        $edits = new Edits($this->textFormat);
        $node = $this->parser->parseSourceFile((string) $code);

        $this->updateNamespace($edits, $prototype, $node);
        $this->useStatementUpdater->updateUseStatements($edits, $prototype, $node);
        $this->updateClasses($edits, $prototype, $node);
        return $edits->textEdits();
    }

    private function updateNamespace(Edits $edits, SourceCode $prototype, SourceFileNode $node): void
    {
        $namespaceNode = $node->getFirstChildNode(NamespaceDefinition::class);

        if (null !== $namespaceNode && NamespaceName::root() == $prototype->namespace()) {
            return;
        }

        /** @var $namespaceNode NamespaceDefinition */
        if ($namespaceNode && $namespaceNode->name->getText() == (string) $prototype->namespace()) {
            return;
        }

        if (empty((string) $prototype->namespace())) {
            return;
        }

        if ($namespaceNode) {
            $edits->replace($namespaceNode, 'namespace ' . (string) $prototype->namespace() . ';');
            return;
        }

        $startTag = $node->getFirstChildNode(InlineHtml::class);
        $edits->after($startTag, 'namespace ' . (string) $prototype->namespace() . ';' . PHP_EOL.PHP_EOL);
    }

    private function updateClasses(Edits $edits, SourceCode $prototype, SourceFileNode $node): void
    {
        $classNodes = [];
        $traitNodes = [];
        $interfaceNodes = [];
        $enumNodes = [];
        $lastStatement = null;

        foreach ($node->statementList as $classNode) {
            $lastStatement = $classNode;

            if ($classNode instanceof ClassDeclaration) {
                $name = $classNode->name->getText($node->getFileContents());
                $classNodes[$name] = $classNode;
            }

            if ($classNode instanceof InterfaceDeclaration) {
                $name = $classNode->name->getText($node->getFileContents());
                $interfaceNodes[$name] = $classNode;
            }

            if ($classNode instanceof TraitDeclaration) {
                $name = $classNode->name->getText($node->getFileContents());
                $traitNodes[$name] = $classNode;
            }

            if ($classNode instanceof EnumDeclaration) {
                $name = $classNode->name->getText($node->getFileContents());
                $enumNodes[$name] = $classNode;
            }
        }

        foreach ($prototype->classes()->in(array_keys($classNodes)) as $classPrototype) {
            $this->classUpdater->updateClass($edits, $classPrototype, $classNodes[$classPrototype->name()]);
        }

        foreach ($prototype->interfaces()->in(array_keys($interfaceNodes)) as $classPrototype) {
            $this->interfaceUpdater->updateInterface($edits, $classPrototype, $interfaceNodes[$classPrototype->name()]);
        }

        foreach ($prototype->traits()->in(array_keys($traitNodes)) as $traitPrototype) {
            $this->traitUpdater->updateTrait($edits, $traitPrototype, $traitNodes[$traitPrototype->name()]);
        }

        foreach ($prototype->enums()->in(array_keys($enumNodes)) as $enumPrototype) {
            $this->enumUpdater->updateEnum($edits, $enumPrototype, $enumNodes[$enumPrototype->name()]);
        }

        $classes = array_merge(
            iterator_to_array($prototype->classes()->notIn(array_keys($classNodes))),
            iterator_to_array($prototype->interfaces()->notIn(array_keys($interfaceNodes))),
            iterator_to_array($prototype->traits()->notIn(array_keys($traitNodes))),
            iterator_to_array($prototype->enums()->notIn(array_keys($enumNodes)))
        );

        $index = 0;
        foreach ($classes as $classPrototype) {
            if (substr($lastStatement->getText(), -1) !== PHP_EOL) {
                $edits->after($lastStatement, PHP_EOL);
            }

            if ($index > 0 && $index + 1 == count($classes)) {
                $edits->after($lastStatement, PHP_EOL);
            }
            $edits->after($lastStatement, PHP_EOL . $this->renderer->render($classPrototype));
            $index++;
        }
    }
}
