<?php

namespace Phpactor\Completion\Tests\Unit\Core;

use Phpactor\Completion\Core\Range;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\TestCase;
use RuntimeException;

class SuggestionTest extends TestCase
{
    public function testThrowsExceptionWithInvalidOptions(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid options for suggestion: "foobar" valid options: "short_description", "documentation", "type"');

        Suggestion::createWithOptions('foobar', ['foobar' => 'barfoo']);
    }

    public function testCanBeCreatedWithOptionsAndProvidesAccessors(): void
    {
        $suggestion = Suggestion::createWithOptions('hello', [
            'type' => 'class',
            'short_description' => 'Foobar',
            'class_import' => 'Namespace\\Foobar',
            'label' => 'hallo',
        ]);

        $this->assertEquals('class', $suggestion->type());
        $this->assertEquals('hello', $suggestion->name());
        $this->assertEquals('hallo', $suggestion->label());
        $this->assertEquals('Foobar', $suggestion->shortDescription());
        $this->assertEquals('Namespace\\Foobar', $suggestion->classImport());
        $this->assertEquals('Namespace\\Foobar', $suggestion->fqn());
    }

    public function testDefaults(): void
    {
        $suggestion = Suggestion::create('hello');
        $this->assertEquals('hello', $suggestion->name());
        $this->assertEquals('hello', $suggestion->label());
    }

    public function testCastsToArray(): void
    {
        $suggestion = Suggestion::createWithOptions('hello', [
            'type' => Suggestion::TYPE_CLASS,
            'short_description' => 'Foobar',
            'class_import' => 'Namespace\\Foobar',
            'documentation' => 'foo',
            'label' => 'hallo',
            'range' => Range::fromStartAndEnd(1, 2),
            'snippet' => null,
        ]);

        $this->assertEquals([
            'type' => 'class',
            'short_description' => 'Foobar',
            'documentation' => 'foo',
            'class_import' => 'Namespace\\Foobar',
            'name' => 'hello',
            'label' => 'hallo',
            'range' => [1, 2],
            'info' => '',
            'snippet' => null,
            'name_import' => 'Namespace\\Foobar',
        ], $suggestion->toArray());
    }
}
