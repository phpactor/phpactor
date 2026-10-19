<?php

declare(strict_types=1);

namespace Phpactor\Extension\Pest\Completion;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;

class PestCompletion implements TolerantCompletor
{
    public function __construct(private bool $laravelPluginEnabled)
    {
    }

    private const UNIT_TEST_CASE_CLASS = 'PHPUnit\Framework\TestCase';
    private const FEATURE_TEST_CASE_CLASS = 'Tests\\TestCase';
    private const ILLUMINATE_REFRESH_DATABASE_CLASS = 'Illuminate\\Foundation\\Testing\\RefreshDatabase';

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $path = $source->uri()->path();
        $testCases = $this->getParentTestCaseClasses($path);

        // loop over test cases and yield suggestions

        // for now this is a dummy one to make sure it works
        yield Suggestion::createWithOptions('FOOBAR', [
            'label' => 'A label',
            'short_description' => 'A short description',
            'documentation' => 'The doc',
            'type' => Suggestion::TYPE_VALUE,
            'name_import' => 'name import',
            'priority' => 555,
        ]);
    }

    private function getParentTestCaseClasses(string $path): array
    {
        if (false === mb_strpos($path, 'tests/')) {
            return [];
        }

        if (str_ends_with($path, 'tests/Pest.php')) {
            return $this->featureTestCases();
        }

        if (preg_match('{/tests/Feature/[^/]+Test\.php$}', $path)) {
            return $this->featureTestCases();
        }

        return $this->unitTestCases();
    }

    private function unitTestCases(): array
    {
        return [self::UNIT_TEST_CASE_CLASS];
    }

    private function featureTestCases(): array
    {
        if ($this->laravelPluginEnabled) {
            return [self::FEATURE_TEST_CASE_CLASS, self::ILLUMINATE_REFRESH_DATABASE_CLASS];
        }

        return [self::FEATURE_TEST_CASE_CLASS];
    }
}
