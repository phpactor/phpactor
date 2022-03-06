<?php

namespace Phpactor\CodeTransform\Tests\Adapter\TolerantParser\Refactor;

use Phpactor\CodeTransform\Tests\Adapter\TolerantParser\TolerantTestCase;
use Phpactor\CodeTransform\Adapter\TolerantParser\Refactor\TolerantRenameVariable;
use Phpactor\CodeTransform\Domain\Refactor\RenameVariable;
use Phpactor\CodeTransform\Domain\SourceCode;

class TolerantRenameVariableTest extends TolerantTestCase
{
    /**
     * @dataProvider provideRenameMethod
     */
    public function testRenameVariable(string $test, $name, string $scope = RenameVariable::SCOPE_FILE): void
    {
        [$source, $expected, $offset] = $this->sourceExpectedAndOffset(__DIR__ . '/fixtures/' . $test);

        $renameVariable = new TolerantRenameVariable($this->parser());
        $transformed = $renameVariable->renameVariable(SourceCode::fromString($source), $offset, $name, $scope);

        $this->assertEquals(trim($expected), trim($transformed));
    }

    public function provideRenameMethod()
    {
        return [
            'one instance no context' => [
                'renameVariable1.test',
                'newName'
            ],
            'two instances no context' => [
                'renameVariable2.test',
                'newName'
            ],
            'local scope' => [
                'renameVariable3.test',
                'newName',
                RenameVariable::SCOPE_LOCAL
            ],
            'parameters from declaration' => [
                'renameVariable4.test',
                'newName'
            ],
            'local parameter from body' => [
                'renameVariable4.test',
                'newName',
                RenameVariable::SCOPE_LOCAL
            ],
            'typed parameter' => [
                'renameVariable5.test',
                'newName',
                RenameVariable::SCOPE_LOCAL
            ],
            'anonymous function use' => [
                'renameVariable6.test',
                'newName',
                RenameVariable::SCOPE_LOCAL
            ],
            'anonymous function use within' => [
                'renameVariable7.test',
                'newName',
                RenameVariable::SCOPE_LOCAL
            ],
        ];
    }
}
