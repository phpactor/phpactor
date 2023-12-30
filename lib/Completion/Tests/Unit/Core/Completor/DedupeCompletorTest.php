<?php

namespace Phpactor\Completion\Tests\Unit\Core\Completor;

use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor\ArrayCompletor;
use Phpactor\Completion\Core\Completor\DedupeCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

class DedupeCompletorTest extends TestCase
{
    public function testDeduplicates(): void
    {
        $source = TextDocumentBuilder::create('foobar')->build();
        $offset = ByteOffset::fromInt(10);

        $inner = new ArrayCompletor([
            Suggestion::create('foobar'),
            Suggestion::create('barfoo'),
            Suggestion::create('foobar'),
        ]);
        $dedupe = new DedupeCompletor($inner);
        $suggestions = $dedupe->complete($source, $offset);
        self::assertEquals([
            Suggestion::create('foobar'),
            Suggestion::create('barfoo'),
        ], iterator_to_array($suggestions));
        $this->assertTrue($suggestions->getReturn());
    }

    public function testDedupeWithSuggestionsOfDifferentTypes(): void
    {
        $source = TextDocumentBuilder::create('foobar')->build();
        $offset = ByteOffset::fromInt(10);

        $inner = new ArrayCompletor([
            Suggestion::createWithOptions('foobar', ['type' => Suggestion::TYPE_ENUM]),
            Suggestion::create('barfoo'),
            Suggestion::create('foobar'),
        ]);
        $dedupe = new DedupeCompletor($inner);
        $suggestions = $dedupe->complete($source, $offset);
        self::assertEquals([
            Suggestion::createWithOptions('foobar', ['type' => Suggestion::TYPE_ENUM]),
            Suggestion::create('barfoo'),
            Suggestion::create('foobar'),
        ], iterator_to_array($suggestions));
        $this->assertTrue($suggestions->getReturn());
    }

    public function testDeduplicatesWithFqn(): void
    {
        $source = TextDocumentBuilder::create('foobar')->build();
        $offset = ByteOffset::fromInt(10);

        $inner = new ArrayCompletor([
            Suggestion::create('foobar'),
            Suggestion::createWithOptions('barfoo', [
                'name_import' => 'baf',
            ]),
            Suggestion::create('foobar'),
            Suggestion::createWithOptions('barfoo', [
                'name_import' => 'bosh',
            ]),
        ]);
        $dedupe = new DedupeCompletor($inner, true);
        $suggestions = $dedupe->complete($source, $offset);
        self::assertEquals([
            Suggestion::create('foobar'),
            Suggestion::createWithOptions('barfoo', [
                'name_import' => 'baf',
            ]),
            Suggestion::createWithOptions('barfoo', [
                'name_import' => 'bosh',
            ]),
        ], iterator_to_array($suggestions));
        $this->assertTrue($suggestions->getReturn());
    }
}
