<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Formatter\ObjectFormatter;
use Phpactor\ObjectRenderer\ObjectRendererBuilder;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Bridge\Phpactor\MemberProvider\DocblockMemberProvider;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseClassMemberCompletor;

class WorseClassMemberCompletorTestWithoutSnippetFormatter extends WorseClassMemberCompletorTest
{
    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, array $expected): void
    {
        // Expect all snippets to be null
        foreach ($expected as &$suggestion) {
            if (array_key_exists('snippet', $suggestion)) {
                $suggestion['snippet'] = null;
            }
        }

        $this->assertComplete($source, $expected);
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()
            ->addMemberProvider(new DocblockMemberProvider())
            ->addSource($source)->build();

        return new WorseClassMemberCompletor(
            $reflector,
            $this->formatter(),
            new ObjectFormatter(),
            ObjectRendererBuilder::create()->renderEmptyOnNotFound()->build()
        );
    }
}
