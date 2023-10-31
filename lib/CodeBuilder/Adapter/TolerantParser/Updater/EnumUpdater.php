<?php

namespace Phpactor\CodeBuilder\Adapter\TolerantParser\Updater;

use Microsoft\PhpParser\Node\EnumCaseDeclaration;
use Microsoft\PhpParser\Node\EnumMembers;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Phpactor\CodeBuilder\Domain\Prototype\Constant;
use Phpactor\CodeBuilder\Domain\Prototype\EnumPrototype;
use Phpactor\CodeBuilder\Domain\Renderer;
use Phpactor\CodeBuilder\Adapter\TolerantParser\Edits;

class EnumUpdater
{
    private ClassMethodUpdater $methodUpdater;

    public function __construct(private Renderer $renderer)
    {
        $this->methodUpdater = new ClassMethodUpdater($renderer);
    }

    public function updateCases(
        Edits $edits,
        EnumPrototype $classPrototype,
        EnumMembers $enumMembers
    ): void {
        if (count($classPrototype->cases()) === 0) {
            return;
        }

        $lastConstant = $enumMembers->openBrace;

        $nextMember = null;
        $existingCasesNames = [];

        foreach ($enumMembers as $memberNode) {
            if (null === $nextMember) {
                $nextMember = $memberNode;
            }

            if ($memberNode instanceof EnumCaseDeclaration) {
                $existingCasesNames[] = $memberNode->name->getText();
                $lastConstant = $memberNode;
                $nextMember = next($memberDeclarations) ?: $nextMember;
                prev($memberDeclarations);
            }
        }

        foreach ($classPrototype->cases()->notIn($existingCasesNames) as $case) {
            assert($case instanceof Constant);

            $edits->after(
                $lastConstant,
                PHP_EOL . $edits->indent($this->renderer->render($case), 1)
            );

            if ($classPrototype->constants()->isLast($case) && (
                $nextMember instanceof MethodDeclaration ||
                $nextMember instanceof EnumCaseDeclaration
            )) {
                $edits->after($lastConstant, PHP_EOL);
            }
        }
    }

    public function updateEnum(
        Edits $edits,
        EnumPrototype $classPrototype,
        EnumDeclaration $classNode
    ): void {
        $this->updateCases($edits, $classPrototype, $classNode->enumMembers);
        $this->methodUpdater->updateMethods($edits, $classPrototype, $classNode);
    }
}
