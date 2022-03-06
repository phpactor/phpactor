<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\TokenStringMaps;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\KeywordCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\TextDocument\TextDocument;

class KeywordCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }
    
    public function provideComplete(): Generator
    {
        $keywords = array_merge(array_keys(TokenStringMaps::RESERVED_WORDS), array_keys(TokenStringMaps::KEYWORDS));
        $allKeywords = $this->createExpectedKeywords($keywords);

        yield 'all keywords' => [
            '<?php <>',
            $allKeywords
        ];
        yield 'member' => [
            '<?php class C { <>',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::CLASS_MEMBERS])
        ];
        yield 'member access' => [
            '<?php function F(){ $v-><>',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::MEMBER_ACCESS])
        ];
        yield 'member access with partial name' => [
            '<?php function F(){ $v->p<>',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::MEMBER_ACCESS])
        ];
        yield 'member access after name' => [
            '<?php function F(){ $v->p <>',
            $allKeywords
        ];
        yield 'scoped member access' => [
            '<?php function F(){ joe::<>',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::MEMBER_ACCESS])
        ];
        yield 'scoped member access with partial name' => [
            '<?php function F(){ joe::me<>',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::MEMBER_ACCESS])
        ];
        yield 'scoped member access after name' => [
            '<?php function F(){ joe::me <>',
            $allKeywords
        ];
        yield 'scoped member access after name and brace' => [
            '<?php function F(){ joe::me(<>',
            $allKeywords
        ];
        yield 'inside string' => [
            '<?php $var ="<> ',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::STRING_LITERAL])
        ];
        yield 'inside string expression' => [
            '<?php $var ="{$var->n<> ',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::MEMBER_ACCESS])
        ];
        yield 'var' => [
            '<?php $var<> ',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::VARIABLE])
        ];
        yield 'after var' => [
            '<?php $var <> ',
            $allKeywords
        ];
        yield 'after function braces' => [
            '<?php $func = function() <> ',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::AFTER_ANONYMOUS_FUNC_PARAMS])
        ];
        yield 'after function braces before return statement' => [
            '<?php $func = function() <> :string ',
            $this->createExpectedKeywords(KeywordCompletor::SPECIAL_SCOPES[KeywordCompletor::AFTER_ANONYMOUS_FUNC_PARAMS])
        ];
    }
    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        return new KeywordCompletor();
    }

    private function createExpectedKeywords(array $list): array
    {
        sort($list);
        $keywords = [];
        foreach ($list as $keyword) {
            $keywords[] = [
                'type' => Suggestion::TYPE_KEYWORD,
                'name' => $keyword,
            ];
        }
        return $keywords;
    }
}
