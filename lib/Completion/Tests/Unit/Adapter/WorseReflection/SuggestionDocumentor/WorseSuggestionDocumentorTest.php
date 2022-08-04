<?php

namespace Phpactor\Completion\Tests\Unit\Adapter\WorseReflection\SuggestionDocumentor;

use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Bridge\WorseReflection\SuggestionDocumentor\WorseSuggestionDocumentor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\ObjectRenderer\ObjectRendererBuilder;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseSuggestionDocumentorTest extends TestCase
{
    public function testClassSuggestion(): void 
    {
        $documentation = $this->createDocumentor('<?php /** Hello */class Foobar {}')->document(Suggestion::createWithOptions('Foobar', [
            'type' => Suggestion::TYPE_CLASS,
            'name_import' => 'Foobar',
        ]));
        self::assertNotEmpty($documentation);
    }

    public function testFunctionSuggestion(): void 
    {
        $documentation = $this->createDocumentor('<?php /** Documentation */function boo() {}')->document(Suggestion::createWithOptions('Foobar', [
            'type' => Suggestion::TYPE_FUNCTION,
            'name_import' => 'boo',
        ]));
        self::assertNotEmpty($documentation);
    }

    private function createDocumentor(string $string): WorseSuggestionDocumentor
    {
        return new WorseSuggestionDocumentor(
            ReflectorBuilder::create()->addSource($string)->build(),
            ObjectRendererBuilder::create()->enableInterfaceCandidates()->enableAncestoralCandidates()->renderEmptyOnNotFound()->build()
        );
    }
}
