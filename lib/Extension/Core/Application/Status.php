<?php

namespace Phpactor\Extension\Core\Application;

use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Status
{
    private FilesystemRegistry $registry;

    private ExecutableFinder $executableFinder;

    private PathCandidates $paths;

    private string $workingDirectory;

    private PhpVersionResolver $phpVersionResolver;

    private bool $warnOnDevelop;

    public function __construct(
        FilesystemRegistry $registry,
        PathCandidates $paths,
        string $workingDirectory,
        PhpVersionResolver $phpVersionResolver,
        ExecutableFinder $executableFinder = null,
        bool $warnOnDevelop = true
    ) {
        $this->registry = $registry;
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
        $this->paths = $paths;
        $this->workingDirectory = $workingDirectory;
        $this->phpVersionResolver = $phpVersionResolver;
        $this->warnOnDevelop = $warnOnDevelop;
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

        if (extension_loaded('xdebug')) {
            $diagnostics['bad'][] = 'XDebug is enabled. XDebug has a negative effect on performance.';
        } else {
            $diagnostics['good'][] = 'XDebug is disabled. XDebug has a negative effect on performance.';
        }

        foreach ($this->paths as $configFile) {
            $diagnostics['config_files'][$configFile->path()] = file_exists($configFile->path());
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
            $diagnostics = array_merge($diagnostics, $this->versionInfo($process));
        }

        return $diagnostics;
    }

    private function versionInfo(Process $process): array
    {
        if ($process->getExitCode() !== 0) {
            return [
                'phpactor_version' => 'ERROR: ' . $process->getErrorOutput(),
                'phpactor_is_develop' => false,
            ];
        }

        if (!preg_match('{^(.*)REF(.*?)REF}', $process->getOutput(), $matches)) {
            return [
                'phpactor_version' => $process->getOutput(),
                'phpactor_is_develop' => false,
            ];
        }

        return [
            'phpactor_version' => $matches[1],
            'phpactor_is_develop' => $this->warnOnDevelop && (false !== strpos($matches[2], 'develop'))
        ];
    }
}
