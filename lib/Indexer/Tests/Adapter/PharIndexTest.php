<?php

namespace Phpactor\Indexer\Tests\Adapter;

use Phar;
use Phpactor\Indexer\Model\Query\Criteria;
use Phpactor\Indexer\Tests\IntegrationTestCase;

class PharIndexTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    public function testIndexPhar(): void
    {
        if (ini_get('phar.readonly') != 0) {
            $this->markTestSkipped('PHAR is in readonly not set');
        }

        $this->workspace()->put('phar/index.php', '<?php class Hello{}');
        $this->workspace()->mkdir('repo');

        // create phar
        $phar = new Phar($this->workspace()->path('repo/index.phar'), 0, 'index.phar');
        $phar->buildFromDirectory($this->workspace()->path('phar'));

        $agent = $this->indexAgentBuilder('repo')->buildTestAgent();
        $agent->indexer()->getJob()->run();
        $hellos = iterator_to_array($agent->search()->search(Criteria::shortNameContains('Hello')));
        self::assertCount(1, $hellos);
    }

}
