<?php

namespace Phpactor\Tests\Unit\Extension\LanguageServer\Extension;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServer\Extension\DidChangeHandler;
use Phpactor\LanguageServer\Core\Session\Manager;

class DidChangeHandlerTest extends TestCase
{
    /**
     * @var Manager
     */
    private $manager;
    /**
     * @var DidChangeHandler
     */
    private $handler;

    /**
     * @var DidChangeHandler
     */
    private $handler2;

    public function setUp()
    {
        $this->manager = new Manager('foo');
        $this->handler = new DidChangeHandler($this->manager);
    }

    public function testClearsDiagnostics()
    {
        $this->handler->name();
    }
}
