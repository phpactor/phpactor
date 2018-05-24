<?php

namespace Phpactor\Extension\Core\Application;

use Phpactor\Config\Paths;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class Status
{
    /**
     * @var FilesystemRegistry
     */
    private $registry;

    /**
     * @var ExecutableFinder
     */
    private $executableFinder;

    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var string
     */
    private $workingDirectory;

    public function __construct(
        FilesystemRegistry $registry,
        Paths $paths,
        string $workingDirectory,
        ExecutableFinder $executableFinder = null
    ) {
        $this->registry = $registry;
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
        $this->paths = $paths;
        $this->workingDirectory = $workingDirectory;
    }

    public function check(): array
    {
        $filesystems = $this->registry->names();
        $diagnostics = [
            'filesystems' => $filesystems,
            'cwd' => $this->workingDirectory,
            'config_files' => [],
            'good' => [],
            'bad' => [],
        ];

        if (in_array(SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER, $filesystems)) {
            $diagnostics['good'][] = 'Composer detected - faster class location and more features!';
        } else {
            $diagnostics['bad'][] = 'Composer not found - some functionality will not be available (e.g. class creation) and class location will fallback to scanning the filesystem - this can be slow.';
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

        foreach ($this->paths->configFiles() as $configFile) {
            $diagnostics['config_files'][$configFile] = file_exists($configFile);
        }

        if ($path = $this->executableFinder->find('git')) {
            $process = new Process('git log -1 --pretty=format:"%h (%ad) %f" --date=relative', __DIR__ . '/../../../..');
            $process->run();
            if ($process->getExitCode() === 0) {
                $diagnostics['phpactor_version'] = $process->getOutput();
            } else {
                $diagnostics['phpactor_version'] = 'ERROR: ' . $process->getErrorOutput();
            }
        }

        return $diagnostics;
    }
}
