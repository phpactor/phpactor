<?php

namespace Phpactor\WorseReflection\Tests\Integration\Core\Inference;

use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\Inference\ExpressionEvaluator;
use Microsoft\PhpParser\Node\Expression;

class ExpressionEvaluatorTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate(string $source, $expectedResult): void
    {
        $rootNode = $this->parseSource('<?php ' . $source, $this->workspace()->path('foo.php'));
        $node = $rootNode->getFirstDescendantNode(Expression::class);

        $evaluator = new ExpressionEvaluator();
        $result = $evaluator->evaluate($node);

        $this->assertSame($expectedResult, $result);
    }

    public function provideEvaluate()
    {
        // booleans
        yield 'boolean 1' => [
            'true;',
            true
        ];

        yield 'boolean 2' => [
            'false;',
            false
        ];

        // numbers
        yield 'int' => [
            '1234;',
            1234
        ];

        yield 'float' => [
            '1234.1234;',
            1234.1234
        ];

        yield 'false' => [
            'false;',
            false
        ];

        yield 'addition' => [
            '1 + 2',
            3
        ];

        yield 'subtraction' => [
            '3 - 1',
            2
        ];

        yield 'mod' => [
            '3 % 1',
            0
        ];

        yield 'division' => [
            '4 / 2',
            2
        ];

        // pre and post increment
        yield 'post increment' => [
            '4++',
            4
        ];
        yield 'pre increment' => [
            '++4',
            5
        ];
        yield 'post deincrement' => [
            '4--',
            4
        ];
        yield 'pre deincrement' => [
            '--4',
            3
        ];

        // logical operators
        yield 'true && false' => [
            'true && false;',
            false
        ];

        yield 'true and false' => [
            'true and false;',
            false
        ];

        yield 'true and true' => [
            'true and true;',
            true
        ];

        yield 'or' => [
            'true or false;',
            true
        ];

        yield 'or ||' => [
            'true || false',
            true
        ];

        yield '! negation' => [
            '!false',
            true
        ];

        yield 'xor' => [
            'true xor false',
            true
        ];

        // comparison
        yield 'equal' => [
            '1 == 1',
            true
        ];

        yield 'identical' => [
            '1 === 1',
            true
        ];

        yield 'not identical' => [
            '1 !== 1',
            false
        ];

        yield 'less than' => [
            '1 < 1',
            false
        ];

        yield 'greater than' => [
            '1 > 2',
            false
        ];

        yield 'greater than equal' => [
            '1 >= 2',
            false
        ];

        yield 'less than equal' => [
            '1 <= 2',
            true
        ];

        yield 'not indentical' => [
            '1 == 1',
            true
        ];
        // strings
        yield 'string and string' => [
            '"hello";',
            'hello'
        ];

        // bitwise
        yield 'Bitwise and' => [
            'true & true',
            1
        ];

        yield 'Bitwise or' => [
            'true | true',
            1
        ];

        yield 'Neg' => [
            '~ 1',
            -2
        ];

        yield 'Bitwise xor' => [
            'true ^ true',
0
        ];

        yield 'Bitwise shift left' => [
            '1 << 2',
4
        ];

        yield 'Bitwise shift right' => [
            'true << true',
2
        ];


        yield 'concatenation' => [
            '"hello" . \'foo\';',
            'hellofoo'
        ];

        yield 'concatenation 2' => [
            '"hello" .= \'foo\';',
            'hellofoo'
        ];

        // instanceof
        yield 'instanceof is always true' => [
            'Foobar instanceof Foobar',
            true
        ];

        yield 'variables always evalyate to true' => [
            '$foo',
            true
        ];

        // parenthesis
        yield 'parenthesis' => [
            '(10)',
            10
        ];

        yield 'ternary' => [
            'true ? "hello" : "goodbye"',
            'hello'
        ];

        yield 'ternary short' => [
            'true ?: "goodbye"',
                true
        ];

        // complex expressions
        yield 'linear math' => [
            '10 - 5 < 6',
            true
        ];

        yield 'complex expresion' => [
            '(5 > 3) AND (10 - 5 < 6)',
            true
        ];

        yield 'missing token' => [
            '(5 > 3) AND (',
            false
        ];

        yield 'division by zero returns 0 to avoid crash' => [
            '5 / 0',
            0
        ];

        yield 'modulo by zero returns 0 to avoid crash' => [
            '5 / 0',
            0
        ];

        yield 'division by unresolable constant returns 0' => [
            '5 / SOME_CONSTANT',
            0
        ];

        yield 'magic constants: __DIR__' => [
            '(__DIR__)',
            rtrim($this->workspace()->path(''), '/')
        ];

        yield 'magic constants: __FILE__' => [
            '(__FILE__)',
            $this->workspace()->path('foo.php')
        ];
    }
}
