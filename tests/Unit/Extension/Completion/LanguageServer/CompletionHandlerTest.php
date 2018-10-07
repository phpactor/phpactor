<?php

namespace Phpactor\Tests\Unit\Extension\Completion\LanguageServer;

use Generator;
use LanguageServerProtocol\CompletionItem;
use LanguageServerProtocol\CompletionList;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\TextDocumentItem;
use PHPUnit\Framework\TestCase;
use Phpactor\Completion\Core\Completor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\Completion\LanguageServer\CompletionHandler;
use Phpactor\LanguageServer\Core\Handler;
use Phpactor\LanguageServer\Core\Session\Manager;
use Phpactor\WorseReflection\ReflectorBuilder;

class CompletionHandlerTest extends TestCase
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var TextDocumentItem
     */
    private $document;

    /**
     * @var Position
     */
    private $position;

    /**
     * @var Reflector
     */
    private $reflector;


    public function setUp()
    {
        $this->manager = new Manager();
        $this->manager->initialize('foo');
        $this->document = new TextDocumentItem();
        $this->document->uri = 'test';
        $this->document->text = 'hello';
        $this->position = new Position(1, 1);

        $this->reflector = ReflectorBuilder::create()->build();

        $this->manager->current()->workspace()->open($this->document);
    }

    public function testHandleNoSuggestions()
    {
        $handler = $this->create([]);
        $generator = $handler->__invoke($this->document, $this->position);
        $this->assertInstanceOf(CompletionList::class, $generator->current());
        $list = $generator->current();
        $this->assertEquals([], $list->items);
    }

    public function testHandleSuggestions()
    {
        $handler = $this->create([
            Suggestion::create('hello'),
            Suggestion::create('goodbye'),
        ]);
        $generator = $handler->__invoke($this->document, $this->position);
        $this->assertInstanceOf(CompletionList::class, $generator->current());
        $list = $generator->current();
        $this->assertEquals([
            new CompletionItem('hello'),
            new CompletionItem('goodbye'),
        ], $list->items);
    }

    private function create(array $suggestions): Handler
    {
        return new CompletionHandler($this->manager, new class($suggestions) implements Completor {
            private $suggestions;
            public function __construct(array $suggestions) 
            {
                $this->suggestions = $suggestions;
            }

            public function complete(string $source, int $offset): Generator
            {
                foreach ($this->suggestions as $suggestion) {
                    yield $suggestion;
                }
            }
        }, $this->reflector);
    }
}
