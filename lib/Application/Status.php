<?php

namespace Phpactor\Application;

use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;

class Status
{
    /**
     * @var FilesystemRegistry
     */
    private $registry;

    public function __construct(
        FilesystemRegistry $registry
    ) {
        $this->registry = $registry;
    }

    public function check(): array
    {
        $filesystems = $this->registry->names();
        $diagnostics = [
            'filesystems' => $filesystems,
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

        return $diagnostics;
    }
}
