<?php

namespace Phpactor\Extension\Behat\Behat;

use Generator;
use Symfony\Component\Yaml\Yaml;

class BehatConfig
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    private function findContexts(string $path): Generator
    {
        $paths = [
            $path,
            $path . '.dist'
        ];

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            yield from $this->readConfig($path);
        }
    }

    private function readConfig(string $path): Generator
    {
        $contents = Yaml::parseFile($path);

        if (empty($contents)) {
            return;
        }

        if (isset($contents['imports'])) {
            foreach ((array)$contents['imports'] as $importPath) {
                yield from $this->readConfig(dirname($this->path) . '/' . $importPath);
            }
        }

        yield from $this->parseContexts($contents);
    }

    private function parseContexts(array $config): Generator
    {
        foreach ($config as $profile) {
            if (!isset($profile['suites'])) {
                continue;
            }

            foreach ($profile['suites'] as $suiteName => $suite) {
                if (!isset($suite['contexts'])) {
                    continue;
                }
                foreach ($suite['contexts'] as $key => $context) {
                    // note this isn't tested
                    if (is_array($context)) {
                        $context = key($context);
                    }

                    yield new Context($suiteName, $context);
                }
            }
        }
    }

    /**
     * @return Context[]
     */
    public function contexts(): array
    {
        $contexts = [];
        foreach ($this->findContexts($this->path) as $context) {
            $contexts[] = $context;
        }
        return $contexts;
    }
}
