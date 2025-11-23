<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseConstantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Generator;
use Phpactor\TextDocument\TextDocument;

class WorseConstantCompletorTest extends TolerantCompletorTestCase
{
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    #[DataProvider('provideCouldNotComplete')]
    public function testCouldNotComplete(string $source): void
    {
        $this->assertCouldNotComplete($source);
    }

    public static function provideComplete(): Generator
    {
        define('PHPACTOR_TEST_FOO', 'Hello');
        yield 'constant' => [
            '<?php PHPACTOR_TEST_<>', [
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'PHPACTOR_TEST_FOO',
                    'short_description' => "PHPACTOR_TEST_FOO = 'Hello'",
                ]
            ]
        ];

        define('namespaced\PHPACTOR_NAMESPACED', 'Hello');
        yield 'namespaced constant' => [
            '<?php PHPACTOR_NAME<>', [
                [
                    'type' => Suggestion::TYPE_CONSTANT,
                    'name' => 'PHPACTOR_NAMESPACED',
                    'short_description' => "namespaced\PHPACTOR_NAMESPACED = 'Hello'",
                ]
            ]
        ];
    }

    public static function provideCouldNotComplete(): Generator
    {
        yield 'non member access' => [ '<?php $hello<>' ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        return new WorseConstantCompletor($this->formatter());
    }
}
