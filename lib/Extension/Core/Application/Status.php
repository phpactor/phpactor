<?php

namespace Phpactor\Extension\Core\Application;

use Composer\InstalledVersions;
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Composer\XdebugHandler\XdebugHandler;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Status
{
    private ExecutableFinder $executableFinder;

    public function __construct(
        private FilesystemRegistry $registry,
        private PathCandidates $paths,
        private string $workingDirectory,
        private PhpVersionResolver $phpVersionResolver,
        ExecutableFinder $executableFinder = null,
        private bool $warnOnDevelop = true
    ) {
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
    }

    public function check(): array
    {
        $filesystems = $this->registry->names();
        $diagnostics = [
            'filesystems' => $filesystems,
            'cwd' => $this->workingDirectory,
            'php_version' => $this->phpVersionResolver->resolve(),
            'config_files' => [],
            'good' => [],
            'bad' => [],
        ];

        if (in_array(SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER, $filesystems)) {
            $diagnostics['good'][] = 'Composer detected - Phpactor could work faster without an index';
        } else {
            $diagnostics['bad'][] = 'Composer not found - some functionality will not be available (e.g. class creation) and class location will fallback to scanning the filesystem if index not enabled - this can be slow. Make sure you\'ve run `composer install` in your project!';
        }

        if (in_array(SourceCodeFilesystemExtension::FILESYSTEM_GIT, $filesystems)) {
            $diagnostics['good'][] = 'Git detected - enables faster refactorings in your repository scope!';
        } else {
            $diagnostics['bad'][] = 'Git not detected. Some operations which would have been better scoped to your project repository will now include vendor paths.';
        }

        if (XdebugHandler::isXdebugActive()) {
            $diagnostics['bad'][] = 'XDebug is enabled. XDebug has a negative effect on performance.';
        } else {
            $diagnostics['good'][] = 'XDebug is disabled. XDebug has a negative effect on performance.';
        }

        foreach ($this->paths as $configFile) {
            $diagnostics['config_files'][$configFile->path()] = file_exists($configFile->path());
        }

        $diagnostics = $this->resolveVersion($diagnostics);

        return $diagnostics;
    }
    /**
     * @param array<string,string> $diagnostics
     * @return array<string,string>
     */
    private function resolveVersion(array $diagnostics): array
    {
        if (\Phar::running() !== '') {
            $diagnostics['phpactor_version'] = InstalledVersions::getVersion('phpactor/phpactor');
            return $diagnostics;
        }

        if ($path = $this->executableFinder->find('git')) {
            $process = new Process(
                [
                    'git',
                    'log',
                    '-1',
                    '--pretty=format:"%h (%ad) %f REF(%D)REF',
                    '--date=relative'
                ],
                __DIR__ . '/../../../..'
            );
            $process->run();
            return array_merge($diagnostics, $this->versionInfo($process));
        }

        return $diagnostics;
    }
    /**
     * @return array<string,string>
     */
    private function versionInfo(Process $process): array
    {
        if ($process->getExitCode() !== 0) {
            return [
                'phpactor_version' => 'ERROR: ' . $process->getErrorOutput(),
            ];
        }

        if (!preg_match('{^(.*)REF(.*?)REF}', $process->getOutput(), $matches)) {
            return [
                'phpactor_version' => $process->getOutput(),
            ];
        }

        return [
            'phpactor_version' => $matches[1],
            'phpactor_is_develop' => $this->warnOnDevelop && (str_contains($matches[2], 'develop'))
        ];
    }
}
