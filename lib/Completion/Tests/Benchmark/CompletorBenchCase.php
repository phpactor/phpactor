<?php

namespace Phpactor\Completion\Tests\Benchmark;

use Phpactor\Completion\Core\Completor;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocumentBuilder;

abstract class CompletorBenchCase
{
    private $source;

    private $offset;

    private Completor $completor;

    public function setUp($params): void
    {
        $source = file_get_contents(__DIR__ . '/' . $params['source']);
        list($source, $offset) = ExtractOffset::fromSource($source);
        $this->source = $source;
        $this->offset = $offset;
        $this->completor = $this->create($source);
    }

    /**
     * @ParamProviders({"provideComplete"})
     * @BeforeMethods({"setUp"})
     * @Revs(1)
     * @Iterations(10)
     * @OutputTimeUnit("milliseconds")
     */
    public function benchComplete($params): void
    {
        iterator_to_array($this->completor->complete(
            TextDocumentBuilder::create($this->source)->build(),
            ByteOffset::fromInt($this->offset)
        ));
    }

    public function provideComplete()
    {
        return [
            'short' => [
                'source' => 'Code/Short.php.test',
            ],
            'long' => [
                'source' => 'Code/Example1.php.test',
            ],
        ];
    }

    abstract protected function create(string $source): Completor;
}
