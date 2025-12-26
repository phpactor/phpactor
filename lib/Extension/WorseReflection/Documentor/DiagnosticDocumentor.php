<?php

namespace Phpactor\Extension\WorseReflection\Documentor;

use Phpactor\Container\Container;
use Phpactor\Extension\Debug\Model\DocHelper;
use Phpactor\Extension\Debug\Model\Documentor;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\ReflectorBuilder;
use ReflectionClass;
use function Amp\Promise\wait;

class DiagnosticDocumentor implements Documentor
{
    /**
     * @param array<string,mixed> $providerIds
     */
    public function __construct(
        private readonly Container $container,
        private readonly array $providerIds
    ) {
    }

    public function document(string $commandName = ''): string
    {
        $docs = [
            '.. _diagnostics:',
            '',
            'Diagnostics',
            '===========',
            "\n",
            ".. This document is generated via the `$commandName` command",
            "\n",
            '.. contents::',
            '   :depth: 2',
            '   :backlinks: none',
            '   :local:',
            "\n",
        ];
        foreach (array_keys($this->providerIds) as $providerId) {
            $documentation = $this->documentProvider($this->container->expect($providerId, DiagnosticProvider::class));
            $docs[] = $documentation;
        }
        return implode("\n", $docs);
    }

    private function documentProvider(DiagnosticProvider $provider): string
    {
        $reflection = new ReflectionClass($provider);
        $docs = [];
        $docs[] = DocHelper::title('-', sprintf('%s', $reflection->getShortName()));
        $docs[] = '';
        $docs[] = trim((string)preg_replace('{(^/\*\*)|(\s+\*\s)|(\s+\*/$)}m', ' ', trim((string)$reflection->getDocComment())));
        $docs[] = '';
        $docs[] = '.. tabs::';
        $docs[] = '';
        foreach ($provider->examples() as $example) {
            if ($example->valid) {
                continue;
            }
            $tab = [];
            $docs[] = sprintf('    .. tab:: %s', $example->title);
            $docs[] = '        ' . DocHelper::indent(8, implode("\n", $this->buildExample($example, $provider)));
        }
        return implode("\n", $docs);
    }

    /**
     * @return Diagnostics<Diagnostic>
     */
    private function diagnostics(DiagnosticExample $example, DiagnosticProvider $provider): Diagnostics
    {
        $reflector = ReflectorBuilder::create()->addSource($example->source)->addDiagnosticProvider($provider)->build();
        return wait($reflector->diagnostics(TextDocumentBuilder::fromPathAndString('file:///test', $example->source)));
    }
    /**
     * @return array<int,string>
     */
    private function buildExample(DiagnosticExample $example, DiagnosticProvider $provider): array
    {
        $ex = [];
        $ex[] = '';
        $ex[] = '.. code-block:: php';
        $ex[] = '';
        $ex[] = '    ' . DocHelper::indent(4, $example->source);
        $ex[] = '';
        $diagnostics = $this->diagnostics($example, $provider);
        if (!$diagnostics->count()) {
            return $ex;
        }
        $ex[] = 'Diagnostic(s):';
        $ex[] = '';
        foreach ($diagnostics as $diagnostic) {
            $ex[] = sprintf('- ``%s``: ``%s``', $diagnostic->severity()->toString(), $diagnostic->message());
        }
        $ex[] = '';
        return $ex;
    }
}
